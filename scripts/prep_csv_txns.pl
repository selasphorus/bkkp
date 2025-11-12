#!/usr/bin/perl

# The purpose of this file is to format raw CSV files (downloaded from financial institutions) to make them ready for import into bkkp
# Last mods: 10/17/25
#
# Recommended filename format: YYYY-COMPANY-ACCT-description.csv or YYYYMM-COMPANY-ACCT.csv
# Examples: 2024-USAA-7574-annual.csv, 202409-USAA-7574.csv, 20240901-20240930-USAA-7574.csv
# Usage: ptfi {filename or directory}
# The script will detect the actual date range from transaction data and name output files accordingly

use strict;
use warnings;
use FindBin qw($RealBin);
use lib "$RealBin/../lib";
use CSVUtils qw(get_or_prompt_headers headers_match combine_csv_files);
use Text::CSV;
use File::Basename;
use File::Path qw(make_path);
use POSIX qw/strftime/;

# Transaction type classifications
# Maps source transaction types to debit/credit and sign convention
my %type_classifications = (
    # Transaction types and their categories
    # ---
    # Standard debit types
    'purchase'     => 'debit',
    'sale'         => 'debit',
    'withdrawal'   => 'debit',
    'installment'  => 'debit',
    'fee'          => 'debit',
    'debit'        => 'debit',
    'other'        => 'debit',  # Default assumption
    # Standard credit types
    'payment'      => 'credit',
    'credit'       => 'credit',
    'return'       => 'credit',
    'refund'       => 'credit',
    'deposit'      => 'credit',
);

# Check if input file name is provided as command-line argument
if (@ARGV < 1) { die "Usage: $0 <input_file.csv or directory>\n"; }

my $input_arg = $ARGV[0];
my $account_name = $ARGV[1] || ""; # optional: set the account name as an arg
my $input_filename;
my $combined_file_created = 0;

# Check if input is a directory - if so, combine files first
if (-d $input_arg || -d "data/raw/$input_arg") {
    print "Input is a directory. Combining CSV files...\n";
    $input_filename = combine_csv_files($input_arg);
    $combined_file_created = 1;
} else {
    $input_filename = $input_arg;
}

# Normalize filename string for parsing: replace spaces with hyphens
my $normalized_filename = $input_filename;
$normalized_filename =~ s/\s+/-/g;  # Replace spaces with hyphens

# Extract filename without path
my ($base_filename) = fileparse($normalized_filename, qr/\.[^.]*/);

# Try to extract components from filename
#my @parts = split('-', $base_filename);
my ($year, $company, $acct);

# Detect Apple Card format (contains "Card" and "Transactions")
if ($base_filename =~ /^([A-Za-z0-9]+)-+Card-+Transactions/i) {
    $company = uc($1);  # First part is company name
    # Try to extract year from anywhere in the filename
    if ($base_filename =~ /(\d{4})/) {
        $year = $1;
    }
    # Account will be prompted for
    print "Detected Apple Card format: company=$company, year=$year\n";
}
# Handle COMPANY-ACCT_YYYYMMDD-YYYYMMDD format
elsif ($base_filename =~ /^([A-Za-z0-9]+)-(\d+)_(\d{4})/) {
    $company = uc($1);
    $acct = uc($2);
    $year = $3;  # Extract year from start date
}
# Handle COMPANY-ACCT_ActivityYYYYMMDD_YYYYMMDD_YYYYMMDD format
elsif ($base_filename =~ /^([A-Za-z0-9]+)-(\d+)_Activity(\d{4})/) {
    $company = uc($1);
    $acct = uc($2);
    $year = $3;  # Extract year from start date
}
# Handle COMPANY-ACCT-transactions_YYYYMMDD format
elsif ($base_filename =~ /^([A-Za-z0-9]+)-(\d+)-transactions_(\d{4})/) {
    $company = uc($1);
    $acct = uc($2);
    $year = $3;
}
# Handle YYYY-COMPANY-ACCT format
elsif ($base_filename =~ /^(\d{4})-([A-Za-z0-9]+)-(\d+)/) {
    $year = $1;
    $company = uc($2);
    $acct = uc($3);
}
# Fallback: try splitting by dash
else {
    my @parts = split('-', $base_filename);
    if (@parts >= 2) {
        $company = uc($parts[0]);
        $acct = uc($parts[1]);
    }
}

# If year not found in filename, try to extract from directory path
if (!defined $year || $year !~ /^\d{4}$/) {
    if ($input_filename =~ m{/(\d{4})/}) {
        $year = $1;
        print "Detected year from directory path: $year\n";
    }
}

# If processing a combined file, extract company from directory path
if ($base_filename =~ /combined$/i && $input_filename =~ m{/(\d{4})/([^/]+)/}) {
    $year = $1 unless defined $year;
    $company = uc($2);
    print "Detected company from directory path: $company\n";
}

# Validate/prompt for year
if (!defined $year || $year !~ /^\d{4}$/) {
    while (!defined $year || $year !~ /^\d{4}$/) {
        print "Year not found or invalid in filename.\n";
        print "Enter year in YYYY format (e.g., 2024): ";
        chomp($year = <STDIN>);
    }
}

# Validate/prompt for company
while (!defined $company || $company eq '' || $company !~ /^[A-Za-z0-9]+$/) {
    print "Company not found or invalid in filename.\n" if defined $company && $company ne '';
    print "Enter company name (alphanumeric, e.g., USAA, Chase): ";
    chomp($company = <STDIN>);
}
$company = uc($company);  # Normalize to uppercase

# Validate/prompt for account
while (!defined $acct || $acct eq '' || $acct !~ /^\d+$/) {
    print "Account identifier not found or invalid in filename.\n" if defined $acct && $acct ne '';
    print "Enter account last 4 digits (numeric, e.g., 1234): ";
    chomp($acct = <STDIN>);
    $acct =~ s/[\r\n]+$//;  # Remove all trailing whitespace including CR and LF
}
$acct = uc($acct);  # Normalize to uppercase

# Prompt for data source
print "What's the data source? (default: csv/ptfi): ";
chomp(my $data_source = <STDIN>);
$data_source = "csv/ptfi" if $data_source eq '';  # Use default if empty

# Now locate the actual input file
my $input_file;
if (-e $input_filename) {
    # File exists as specified (full or relative path)
    $input_file = $input_filename;
} else {
    # Try to find it in data/raw/{year}/
    my $expected_path = "data/raw/$year/$input_filename";
    if (-e $expected_path) {
        $input_file = $expected_path;
        print "Found file at: $input_file\n";
    } else {
        die "Cannot find file: tried '$input_filename' and '$expected_path'\n";
    }
}

# Now that we've located the file, rename it if it has spaces
if ($input_file =~ /\s/) {
    my ($name, $dir, $ext) = fileparse($input_file, qr/\.[^.]*/);
    $name =~ s/\s+/-/g;
    my $normalized_file = $dir . $name . $ext;
    
    rename($input_file, $normalized_file)
        or die "Cannot rename '$input_file' to '$normalized_file': $!";
    print "Renamed file to: $normalized_file\n";
    $input_file = $normalized_file;
}

if ($input_file =~ /\.txt$/i) {
    print "$input_file is a txt doc, not a CSV >> convert it.\n";
    my @records = parse_text_statement($input_file, $year);
    my @csv_lines = records_to_csv(@records);
    
    # Either write to a temp file or process directly
    # Option 1: Write to temp CSV file
    my $temp_csv = "$input_file.csv";
    open(my $out, '>', $temp_csv) or die "Cannot write $temp_csv: $!";
    print $out join("\n", @csv_lines), "\n";
    close($out);
    
    # Then continue processing the temp CSV file
    $input_file = $temp_csv;
} elsif ($input_file =~ /\.csv$/i) {
    # Normalize CSV headers if needed
    #normalize_csv_headers($input_file); # wip
}

# Create CSV object
my $csv = Text::CSV->new({ binary => 1, auto_diag => 1, eol => "\n" }) 
    or die "Cannot use CSV: " . Text::CSV->error_diag();

# Open input file
open(my $input_fh, "<", $input_file) or die "Can't open $input_file: $!";

# Extract path components for output file construction
my ($filename, $directory) = fileparse($input_file, qr/\.[^.]*/);
my $date_today = strftime("%Y%m%d", localtime);

# Read header row from input file
my $header_row = $csv->getline($input_fh);
#print "header_row is of type: ";
#print ref $header_row;
#print "\n";

# Normalize column names and find required indexes (indices?)
my ($date_index, $amount_index, $type_index) = (-1, -1, -1);
my @date_columns;  # Track all date-related columns

# Track indices for all description-related fields
my %desc_indices = (
    'merchant' => -1,
    'investment_name' => -1,
    'description' => -1,
    'transaction_description' => -1,
    'original_description' => -1,
    'statement_description' => -1,
    'purchased_by' => -1,
);

foreach my $i (0..$#$header_row) {
    my $header = $header_row->[$i];
    
    # Normalize: lowercase and underscores
    $header = lc($header);
    $header =~ s/\s+/_/g;
    $header =~ s/[^\w]/_/g;
    
    # Find indexes before renaming (with priority order)
    # For date: prefer transaction_date > date > post_date
    if ($header eq 'transaction_date') {
        $date_index = $i if $date_index == -1;
        push @date_columns, $i;
    } elsif ($header eq 'date') {
        $date_index = $i if $date_index == -1;
        push @date_columns, $i;
    } elsif ($header eq 'post_date' || $header eq 'clearing_date') {
        # Rename clearing_date to post_date
        $header = 'post_date' if $header eq 'clearing_date';
        $date_index = $i if $date_index == -1;
        push @date_columns, $i;
    } elsif ($header eq 'type') {
        $type_index = $i;
        $header = 'source_type';  # Rename to preserve original type
    } elsif ($header eq 'amount' || $header eq 'amount__usd_') {
        $amount_index = $i;
        $header = 'amount_signed';  # Rename after capturing index
    } elsif ($header eq 'description' || $header eq 'merchant' || 
        $header eq 'statement_description' || $header eq 'original_description' ||
        $header eq 'transaction_description' || $header eq 'purchased_by' ||
        $header eq 'investment_name') {
        # (Don't modify these headers yet - we'll handle them specially later)
        # Track the index for this description field
        $desc_indices{$header} = $i;
    }
    # TODO: mod to deal w/ reference_num/transaction_id fields
    
    # Update the header in the array with the normalized version
    $header_row->[$i] = $header;
}

# If "date" or "amount_signed" column not found, exit with an error
if ($date_index == -1) { die "Column 'date' not found in input file."; }
if ($amount_index == -1) { die "Column 'amount_signed' not found in input file."; }

# Read and store CSV content
my @content;
while (my $row = $csv->getline($input_fh)) {
    # Normalize all date columns
    foreach my $col_idx (@date_columns) {
        if (defined $row->[$col_idx]) {
            $row->[$col_idx] = normalize_date($row->[$col_idx]);
        }
    }
    # Strip dollar signs and commas from amount column
    if ($amount_index >= 0 && defined $row->[$amount_index]) {
        $row->[$amount_index] =~ s/[\$,]//g;
    }
    push @content, $row;
}

# Check if file has any data
unless (@content) {
    die "Error: Input file contains no data rows\n";
}

# Sort the CSV content by date in ascending order
my @sorted_content = sort {
    $a->[$date_index] cmp $b->[$date_index]; # Compare date values
} @content;

# Analyze date range in the data
my ($min_date, $max_date);
foreach my $row (@sorted_content) {
    my $date = $row->[$date_index];
    $min_date = $date if !defined $min_date || $date lt $min_date;
    $max_date = $date if !defined $max_date || $date gt $max_date;
}

# Extract years from date range (now always YYYY-MM-DD)
my ($min_year) = $min_date =~ /^(\d{4})/;
my ($max_year) = $max_date =~ /^(\d{4})/;

# Group transactions by year
my %transactions_by_year;
foreach my $row (@sorted_content) {
    my $date = $row->[$date_index];
    my ($tx_year) = $date =~ /^(\d{4})/;
    push @{$transactions_by_year{$tx_year}}, $row if defined $tx_year;
}

# Track if we've warned about missing account mapping
my $warned_about_account = 0;

# Track unrecognized types we've already prompted for
my %prompted_types;

# Process each year's transactions separately
foreach my $tx_year (sort keys %transactions_by_year) {
    my @year_transactions = @{$transactions_by_year{$tx_year}};
    
    # Determine date range for this year's transactions
    my $year_min_date = $year_transactions[0]->[$date_index];
    my $year_max_date = $year_transactions[-1]->[$date_index];
    
    # Format dates for filename (YYYYMMDD)
    my $start_date_str = $year_min_date;
    my $end_date_str = $year_max_date;
    $start_date_str =~ s/-//g;
    $end_date_str =~ s/-//g;
    
    # Construct standardized output filename
	my $standard_filename = "${company}-${acct}-transactions";
	my $date_suffix = ($start_date_str eq $end_date_str) 
		? "_${start_date_str}" 
		: "_${start_date_str}-${end_date_str}";
		
	my $year_output_file;
	if ($input_file =~ m{^(.*)/raw/(.*)$}) {
		my $base_path = $1;
		$year_output_file = "$base_path/processed/$tx_year/${standard_filename}${date_suffix}.csv";
	} else {
		# Fallback
		$year_output_file = $directory . "${standard_filename}${date_suffix}.csv";
	}
    
    # Create the output directory if it doesn't exist
    my ($year_out_filename, $year_out_directory) = fileparse($year_output_file);
    make_path($year_out_directory) unless -d $year_out_directory;
    
    # Check if output file already exists
    if (-e $year_output_file) {
        my $mod_time = (stat($year_output_file))[9];
        my $mod_date = strftime("%Y-%m-%d %H:%M:%S", localtime($mod_time));
        
        print "\nWARNING: Output file already exists:\n";
        print "  $year_output_file\n";
        print "  Last modified: $mod_date\n";
        print "Overwrite this file? (y/n): ";
        
        my $response = <STDIN>;
        chomp($response);
        
        unless ($response =~ /^[Yy]$/) {
            print "Skipping $tx_year transactions. Operation aborted for this year.\n";
            next;  # Skip to next year
        }
    }
    
    # Open output file for this year
    open(my $year_output_fh, ">", $year_output_file) or die "Can't open $year_output_file: $!";
    
    # Add new column names to the existing header row
    #my $new_header_row = [@$header_row];  # Make a copy
    #unshift @$new_header_row, 'uid', 'tax_year', 'company', 'last4', 'account', 'amount', 'ttype';
    
    # Build output header, prefixing original description columns with 'raw_'
	# and ensuring date columns have proper names
	my @modified_header = map {
		my $header = $_;
		if (exists $desc_indices{$header}) {
			$header = "raw_$header";
		} elsif ($header eq 'date') {
			$header = 'transaction_date';
		}
		# post_date stays as post_date
		$header;
	} @$header_row;
	
	my $new_header_row = ['uid', 'tax_year', 'company', 'last4', 'account', 'amount', 'ttype', 
						  'description', 'original_description', 'data_source', @modified_header];
    
    # Print revised header to output file
    $csv->print($year_output_fh, $new_header_row);
    
    # Initialize autoincrement counter for this year
    my $counter = 1;
    
    # Process each transaction for this year
    foreach my $row (@year_transactions) {
        
        # Get the date value and extract year (already normalized to YYYY-MM-DD)
        my $date = $row->[$date_index];
        my ($row_year) = $date =~ /^(\d{4})/;
        
		# Get source type if available
		my $source_type = '';
		if ($type_index != -1 && defined $row->[$type_index]) {
			$source_type = $row->[$type_index];
		}
        
        # Extract and format the amount value
        my $amount = $row->[$amount_index];
        
        # Determine correct sign and transaction type
        if ($source_type ne '') {
			my $type_key = lc($source_type);
			#print "Found type_key: '$type_key'\n"; #tft
			
			# Determine what type the current sign indicates
			my $current_ttype = ($amount >= 0) ? 'credit' : 'debit';
			
			if (exists $type_classifications{$type_key}) {
				
				# Based on the given source type_key, do we expect a credit or debit?
				my $expected_ttype = $type_classifications{$type_key};
				
				# If they don't match, flip the sign
				if ($current_ttype ne $expected_ttype) {
					$amount = -$amount;
				}
				
			} else {
				# Unrecognized type - prompt user (only once per type)
				unless (exists $prompted_types{$type_key}) {
					print "\nUnrecognized transaction type: '$source_type'\n";
					print "Amount for this transaction: $amount\n";
					print "Should this be typed as a Credit or Debit? (c/d): ";
					my $response = <STDIN>;
					chomp($response);
					
					my $ttype_classification = ($response =~ /^c/i) ? 'credit' : 'debit';
					$type_classifications{$type_key} = $ttype_classification;
					$prompted_types{$type_key} = 1;
					
					print "\nNote: This classification will be used for all '$source_type' transactions in this run.\n";
					print "To make this permanent, add this line to the \%type_classifications hash in the script:\n";
					print "    '$type_key' => '$ttype_classification',\n\n";
					
					# Now check if sign needs flipping
					if ($current_ttype ne $ttype_classification) {
						$amount = -$amount;
					}
				}
			}
		} else {
			# No type column - determine from amount sign (standard convention)
			#print "No type_key found.\n";
		}
		
		# Determine final ttype from (corrected) amount
		my $ttype = ($amount >= 0) ? 'credit' : 'debit';
        
        $amount = sprintf("%.2f", $amount);
        $row->[$amount_index] = $amount;
        
        # Calculate absolute value of amount
        my $amount_abs = abs($amount);
		
        # Determine transaction type
        $ttype = $amount >= 0 ? 'credit' : 'debit';
        
        # Build description and original_description using our new logic
        my ($description, $original_description) = build_descriptions($row, \%desc_indices);
        
        # Determine account name
        my $account = $account_name;
		if ($account eq "") {
			# Account mappings
			my %account_mappings = (
				'usaa' => {
					'7574' => 'USAA Checking',
					'7566' => 'USAA Savings',
					'3160' => 'USAA Visa',
				},
				'uiecu' => {
					'7029' => 'UIECU Savings',
				},
				'chase' => {
					'3720' => 'Chase "Amazon Prime" Visa [3720]',
					'3943' => 'Chase "Sapphire" Visa [3943]',
					'6386' => 'Chase "Freedom" Visa [6386]',
				},
				'fnbo' => {
				    '3654' => 'FNBO Amtrak card', # WIP
				},
				'apple' => {
				    '1272' => 'Apple Card',
				},
				'amex' => {
				    '2008' => 'American Express "Blue Cash Everyday" [2008]',
				},
				'vanguard' => {
				    #'3654' => 'Vanguard Roth IRA',
				    #'3654' => 'Vanguard Roth IRA',
				}
			);
			
			$account = $account_mappings{lc($company)}{lc($acct)}; #$account = $account_mappings{$company}{$acct}; # could also change mappings to UC
			unless (defined $account) {
				unless ($warned_about_account) {
					warn "No account mapping found for $company-$acct, using default\n";
					$warned_about_account = 1;
				}
				$account = "$company-$acct";
			}
		}
    
		# Create a unique identifier for this transaction
		my $formatted_counter = sprintf("%04d", $counter); # Counter formatted with leading zeros
		my $import_datetime = strftime("%Y%m%d-%H%M%S", localtime);
		my $uid = "$date-$company-$acct-$amount_abs-$import_datetime-$formatted_counter";
		
		# Prepend new columns including our processed descriptions (keeping all original data)
		unshift @$row, $uid, $row_year, $company, $acct, $account, 
                sprintf("%.2f", $amount_abs), $ttype, $description, $original_description, $data_source;
		
		# Write the modified row to the output file
		$csv->print($year_output_fh, $row);
		
		# Increment counter
		$counter++;
	}
    
    # Close this year's output file
    close($year_output_fh);
    
    #last if $counter >= 10; # Break out of the loop after processing 10 rows - for TS
    
    print "Processed " . ($counter - 1) . " transactions for $tx_year. Output written to $year_output_file\n";
}

# Tag the input file as "processed"
my $tag = "processed"; # Consider adding optional input to select a different tag
system("tag", "-a", $tag, $input_file) == 0
    or warn "Failed to add tag to $input_file\n";
    
# Close input file
close($input_fh);

# Clean up combined file if we created it
if ($combined_file_created && -e $input_file) {
    #print "Cleaning up temporary combined file...\n";
    #unlink $input_file or warn "Could not delete $input_file: $!";
}

print "CSV file processing complete.\n";

# ============================================================================
# SUBROUTINES
# ============================================================================

# Subroutine to convert various date formats to YYYY-MM-DD
sub normalize_date {
    my ($date) = @_;
    return $date unless defined $date;
    
    # Already in YYYY-MM-DD format
    if ($date =~ /^\d{4}-\d{2}-\d{2}$/) {
        return $date;
    }
    
    # MM/DD/YYYY format
    if ($date =~ m{^(\d{1,2})/(\d{1,2})/(\d{4})$}) {
        return sprintf("%04d-%02d-%02d", $3, $1, $2);
    }
    
    # M/D/YYYY format (single digit month/day)
    if ($date =~ m{^(\d{1,2})/(\d{1,2})/(\d{2,4})$}) {
        my $year = length($3) == 2 ? "20$3" : $3;
        return sprintf("%04d-%02d-%02d", $year, $1, $2);
    }
    
    # If we can't parse it, return as-is
    warn "Could not normalize date format: $date\n";
    return $date;
}

# Subroutine to parse txt file
sub parse_text_statement {
    my ($filename, $statement_year) = @_;
    my @records;
    
    # Default to current year if not specified
    $statement_year ||= (localtime)[5] + 1900;
    
    # Prompt user about year handling
    print "\n" . "=" x 60 . "\n";
    print "Processing text statement for year $statement_year\n";
    print "Do transactions span from " . ($statement_year - 1) . " into $statement_year? (y/n): ";
    my $spans_years = <STDIN>;
    chomp $spans_years;
    
    my $current_year;
    my $detect_rollover = 0;
    
    if ($spans_years =~ /^y/i) {
        $current_year = $statement_year - 1;
        $detect_rollover = 1;
        print "Will detect year rollover from " . ($statement_year - 1) . " to $statement_year\n";
    } else {
        $current_year = $statement_year;
        print "All transactions will be assigned to $statement_year\n";
    }
    print "=" x 60 . "\n\n";
    
    open(my $fh, '<', $filename) or die "Cannot open $filename: $!";
    
    my $header_seen = 0;
    my $prev_month = undef;
    
    while (my $line = <$fh>) {
        chomp $line;
        next if $line =~ /^\s*$/;
        
        if (!$header_seen && $line =~ /Transaction Date.*Post Date.*Reference Number.*Description.*Amount/i) {
            $header_seen = 1;
            next;
        }
        next unless $header_seen;
        
        if ($line =~ m{
            ^\s*
            (\d{2}/\d{2})\s+
            (\d{2}/\d{2})\s+
            (\S+)\s+
            (.+?)\s+
            \$([0-9,]+\.\d{2})(-)? 
            \s*$
        }x) {
            my ($trans_date, $post_date, $ref_num, $description, $amount, $minus) = 
               ($1, $2, $3, $4, $5, $6);
            
            # Extract month from trans_date
            my ($month) = $trans_date =~ m{^(\d{2})/};
            
            # Detect transition to new year if enabled
            if ($detect_rollover && defined $prev_month && 
                $month < $prev_month && $prev_month >= 11 && $month <= 2) {
                $current_year++;
                $detect_rollover = 0;  # Only roll over once
            }
            $prev_month = $month;
            
            # Add year to dates
            $trans_date .= "/$current_year";
            $post_date .= "/$current_year";
            
            # Clean up amount
            $amount =~ s/,//g;
            # Amounts with trailing minus are credits (positive)
            # Amounts without trailing minus are debits (negative)
            $amount = "-$amount" unless $minus;
            
            # Trim description
            $description =~ s/^\s+|\s+$//g;
            
            push @records, {
                transaction_date => $trans_date,
                post_date   => $post_date,
                ref_num     => $ref_num,
                description => $description,
                amount      => $amount
            };
        }
    }
    
    close($fh);
    return @records;
}

# Helper function to convert records to CSV format
sub records_to_csv {
    my (@records) = @_;
    my @csv_lines;
    
    # Add header
    push @csv_lines, "Transaction Date,Post Date,Reference Number,Description,Amount";
    
    # Add data rows
    foreach my $rec (@records) {
        # Escape description field if it contains commas or quotes
        my $desc = $rec->{description};
        if ($desc =~ /[,"]/) {
            $desc =~ s/"/""/g;  # Escape quotes
            $desc = qq{"$desc"};  # Quote the field
        }
        
        push @csv_lines, join(',',
            $rec->{transaction_date},
            $rec->{post_date},
            $rec->{ref_num},
            $desc,
            $rec->{amount}
        );
    }
    
    return @csv_lines;
}

# Subroutine to normalize CSV headers
sub normalize_csv_headers {
    my ($filename) = @_;
    
    open(my $fh, '<', $filename) or die "Cannot open $filename: $!";
    my $header = <$fh>;
    chomp $header;
    
    # Check if normalization is needed
    my $needs_normalization = 0;
    if ($header =~ /\b(Date|Trans Date)\b/i && $header !~ /Transaction Date/i) {
        $needs_normalization = 1;
    }
    
    if ($needs_normalization) {
        # Read rest of file
        my @lines = <$fh>;
        close($fh);
        
        # Normalize the header
        $header =~ s/\b(?:Trans\s+)?Date\b/Transaction Date/gi;
        
        # Write back to temp file
        my $temp_csv = "$filename.tmp";
        open(my $out, '>', $temp_csv) or die "Cannot write $temp_csv: $!";
        print $out $header, "\n";
        print $out @lines;
        close($out);
        
        # Replace original
        rename($temp_csv, $filename) or die "Cannot rename: $!";
    } else {
        close($fh);
    }
}

# Subroutine to build transaction descriptions
sub build_descriptions {
    my ($row, $desc_indices_ref) = @_;
    my %idx = %$desc_indices_ref;
    
    my $description = '';
    my @original_parts = ();
    
    # Build primary description using priority order
    if ($idx{merchant} >= 0 && defined $row->[$idx{merchant}] && $row->[$idx{merchant}] ne '') {
        $description = $row->[$idx{merchant}];
    } elsif ($idx{investment_name} >= 0 && defined $row->[$idx{investment_name}] && $row->[$idx{investment_name}] ne '') {
        $description = $row->[$idx{investment_name}];
    } elsif ($idx{description} >= 0 && defined $row->[$idx{description}] && $row->[$idx{description}] ne '') {
        $description = $row->[$idx{description}];
    } elsif ($idx{transaction_description} >= 0 && defined $row->[$idx{transaction_description}] && $row->[$idx{transaction_description}] ne '') {
        $description = $row->[$idx{transaction_description}];
    } elsif ($idx{original_description} >= 0 && defined $row->[$idx{original_description}] && $row->[$idx{original_description}] ne '') {
        $description = $row->[$idx{original_description}];
    }
    
    # Trim whitespace from description
    $description =~ s/^\s+|\s+$//g if $description;
    
    # Build original_description by concatenating available fields
    # (excluding the field we used for description)
    my @fields_to_check = (
        ['description', $idx{description}],
        ['statement_description', $idx{statement_description}],
        ['original_description', $idx{original_description}],
        ['transaction_description', $idx{transaction_description}],
        ['purchased_by', $idx{purchased_by}],
    );
    
    # Add merchant and investment_name if they weren't used as primary description
    if ($description ne ($row->[$idx{merchant}] || '')) {
        unshift @fields_to_check, ['merchant', $idx{merchant}];
    }
    if ($description ne ($row->[$idx{investment_name}] || '')) {
        unshift @fields_to_check, ['investment_name', $idx{investment_name}];
    }
    
    foreach my $field (@fields_to_check) {
        my ($name, $index) = @$field;
        if ($index >= 0 && defined $row->[$index] && $row->[$index] ne '') {
            my $value = $row->[$index];
            $value =~ s/^\s+|\s+$//g;  # Trim whitespace
            # Don't add if it's the same as what we used for description
            push @original_parts, $value unless $value eq $description;
        }
    }
    
    my $original_description = join(' // ', @original_parts);
    
    return ($description, $original_description);
}