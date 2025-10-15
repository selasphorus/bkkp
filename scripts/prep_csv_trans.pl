#!/usr/bin/perl

# The purpose of this file is to format raw CSV files (downloaded from financial institutions) to make them ready for import into bkkp
# This is a work in progress. Use with caution. Make backups!
# Last mods: 10/14/25
# Last run: April 2024 -- poorly documented, alas.
#
# Usage: ???

use strict;
use warnings;
use Text::CSV;
use Time::HiRes qw(time); # high precision -- fractional seconds, for building UIDs
use File::Basename;
use POSIX qw/strftime/;

# Check if input file name is provided as command-line argument
if (@ARGV != 1) { die "Usage: $0 <input_file.csv>\n"; }

my $input_file = $ARGV[0];
my $account_name = $ARGV[1] || ""; # optional: set the account name as an arg

# Create CSV objects
my $csv = Text::CSV->new({ binary => 1, auto_diag => 1, eol => "\n" }) or die "Cannot use CSV: " . Text::CSV->error_diag();

# Open input file
open(my $input_fh, "<", $input_file) or die "Can't open $input_file: $!";

# Extract file name components based on hyphens
my ($filename, $directory) = fileparse($input_file, qr/\.[^.]*/);
my ($year, $company, $acct) = split('-', $filename);

# Get today's date with format yyyymmdd
my $date_today = strftime("%Y%m%d", localtime);

# Construct output file name by appending today's date to the input file name
my $output_file = $directory . $filename . "_$date_today.csv";

# Open output file
open(my $output_fh, ">", $output_file) or die "Can't open $output_file: $!";

# Read header row from input file
my $header_row = $csv->getline($input_fh);
#print "header_row is of type: ";
#print ref $header_row;
#print "\n";

# Find indexes of the "Date" and "Amount" columns
my ($date_index, $amount_index) = (-1, -1);
foreach my $i (0..$#$header_row) {
    if ($header_row->[$i] eq 'Date') {
        $date_index = $i;
    } elsif ($header_row->[$i] eq 'Amount') {
        $amount_index = $i;
    } #elsif ($header_row->[$i] eq 'Amount') {
        #$amount_index = $i;
    #}
}

# If "Date" or "Amount" column not found, exit with an error
if ($date_index == -1) { die "Column 'Date' not found in input file."; } #else { print "date_index: [".$date_index."]\n"; }
if ($amount_index == -1) { die "Column 'Amount' not found in input file."; }

# Read and store CSV content
my @content;
while (my $row = $csv->getline($input_fh)) {
    push @content, $row;
    #print "date: ".$row->[$date_index]."\n";
}

# Sort the CSV content by date in ascending order
my @sorted_content = sort {
    # Compare date values
    $a->[$date_index] cmp $b->[$date_index];
} @content;

# Add new column names to the existing header row
my $new_header_row = $header_row;
unshift @$new_header_row, 'uid', 'tax_year', 'company', 'last4', 'account', 'amount_abs', 'ttype';

# Print revised header to output file
$csv->print($output_fh, $new_header_row);

# Initialize autoincrement counter
my $counter = 1;

# Process each line of input CSV
#while (my $row = $csv->getline($input_fh)) {
foreach my $row (@sorted_content) {
    
    # TS: check the date value
    my $date = $row->[$date_index];
    #print "sorted date: ".$row->[$date_index]."\n";
    
    # Extract the original amount value
    my $amount = $row->[$amount_index];
    $amount = sprintf("%.2f", $amount);
    $row->[$amount_index] = $amount;
    
    # Calculate absolute value of amount
    my $amount_abs = abs($amount);
    
    # Determine transaction type
    my $ttype = $amount >= 0 ? 'credit' : 'debit';
    
    my $account = $account_name;
    if ( $account eq "" ) {
    	# Determine value for "Account" column
		my %account_mappings = (
			'usaa' => {
				'7574' => 'USAA Checking',
				'7566' => 'USAA Savings',
				'3160' => 'USAA Visa'
			},
			'chase' => {
				'5555' => 'Sample1',
				'9999' => 'Sample2'
			}
		);

		my $account = $account_mappings{$company}{$acct} // "$company-$acct";
    }
    

    # Format counter with leading zeros
    my $formatted_counter = sprintf("%04d", $counter);
    
    # Prepend new columns to the beginning of the row
    # The disabled version makes a counter-based UID
    #unshift @$row, "$year-$company-$acct-$formatted_counter", $year, $company, $acct, $account, sprintf("%.2f", $amount_abs), $ttype;
    # This WIP version makes a date-and-amount-based UID
    my $timestamp = time();
    my $uid = "$date-$company-$acct-$amount_abs-$timestamp";
    unshift @$row, $uid, $year, $company, $acct, $account, sprintf("%.2f", $amount_abs), $ttype;
    
    # Write the modified row to the output file
    $csv->print($output_fh, $row);
    
    # Increment counter
    $counter++;
    
    #last if $counter >= 10; # Break out of the loop after processing 10 rows - for TS
    
}

# Close input and output files
close($input_fh);
close($output_fh);

print "CSV file processed successfully. Output written to $output_file.\n";
