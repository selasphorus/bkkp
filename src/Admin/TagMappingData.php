<?php

namespace atc\Bkkp\Admin;

/**
 * Tag mapping data for the cleanup process
 * 
 * This class contains all the combo tag mappings separate from the controller logic.
 * This makes it easier to manage and update the large list of tag mappings.
 */
class TagMappingData
{
    /**
     * Get all tag mappings
     * 
     * Format: 'Combo Tag Name' => ['Separate', 'Tag', 'Names']
     * 
     * @return array<string, string[]>
     */
    public static function getMappings(): array
    {
        return [
            // Example mappings - replace with your actual data
            '1099 IT: FFI SttmntVerified' => ['1099', 'IT: FFI', 'SttmntVerified'],
            
            // Add your 150+ mappings below using this format:
            // 'combo-tag' => ['tag1', 'tag2', 'tag3'],
            '1099 ??? Bonus IT: FFI Unverified' => ['1099', 'attn-rqrd', 'Bonus', 'IT: FFI', 'Unverified'],
            
            '*DONE* ??? Fraudulent?' => ['DONE', 'attn-rqrd', 'Maybe Fraudulent'],
            '*DONE* 134-Other' => ['DONE', '134-Other'],
            '*DONE* Biz Billable' => ['DONE', 'Biz', 'Deductible'],
            '*DONE* Biz Billable PayPal PayPal X-check' => ['DONE', 'Biz', 'Deductible', 'PayPal', 'PayPal X-check', 'X-check'],
            '*DONE* CHECK SttmntVerified' => ['DONE', 'CHECK', 'SttmntVerified'],
            '*DONE* DIRECT DEPOSIT SttmntVerified W2' => ['DONE', 'Direct Deposit', 'SttmntVerified', 'W2'],
            '*DONE* MintERR' => ['DONE', 'MintERR'],
            '*DONE* No Receipt' => ['DONE', 'No Receipt'],
            
            '*DONE* OOT4B' => ['DONE', 'biz', 'deductible', 'OOT4B'],
            '*DONE* OOT4B Out of Town' => ['DONE', 'biz', 'deductible', 'OOT4B', 'OOT'],
            '*DONE* OOT4B SttmntVerified' => ['DONE', 'biz', 'deductible', 'OOT4B', 'SttmntVerified'],
            '*DONE* Out of Town' => ['DONE', 'OOT'],
            '*DONE* PayPal PayPal X-check' => ['DONE', 'PayPal', 'PayPal X-check'],
            '*DONE* PayPal PayPal X-check Return/Refund' => ['DONE', 'PayPal', 'PayPal X-check', 'Return/Refund'],
            '*DONE* PayPal PayPal X-check SttmntVerified' => ['DONE', 'PayPal', 'PayPal X-check', 'SttmntVerified'],
            '*DONE* Return/Refund' => ['DONE', 'Return/Refund'],
            '*DONE* Return/Refund X-check OK' => ['DONE', 'Return/Refund', 'X-check OK'],
            '*DONE* SttmntVerified' => ['DONE', 'SttmntVerified'],
            '*DONE* SttmntVerified W2' => ['DONE', 'SttmntVerified', 'W2'],
            '*DONE* X-check OK' => ['DONE', 'X-check OK'],
            'DONE 1099 SttmntVerified' => ['DONE', '1099', 'SttmntVerified'],
            //'xxx' => ['DONE', 'biz', 'deductible'],
            
            "1099 Bonus IT: Birdhive/Misc IT: FFI SttmntVerified" => ['1099', 'Bonus', 'IT: Birdhive/Misc', 'IT: FFI', 'SttmntVerified'],
			"1099 Bonus IT: FFI SttmntVerified" => ['1099', 'Bonus', 'IT: FFI', 'SttmntVerified'],
			"1099 C&T: BUCC Check Verified SttmntVerified" => ['1099', 'C&T: BUCC', 'CHECK', 'Verified', 'SttmntVerified'],
			"1099 C&T: BUCC MintERR USAA-CHK SttmntVerified" => ['1099', 'C&T: BUCC', 'MintERR', 'USAA-CHK', 'SttmntVerified'],
			"1099 C&T: BUCC MintERR USAA-SAV" => ['1099', 'C&T: BUCC', 'MintERR', 'USAA-SAV'],
			"1099 C&T: BUCC MintERR USAA-SAV SttmntVerified" => ['1099', 'C&T: BUCC', 'MintERR', 'USAA-SAV', 'SttmntVerified'],
			"1099 C&T: BUCC Split SttmntVerified" => ['1099', 'C&T: BUCC', 'Split', 'SttmntVerified'],
			"1099 C&T: BUCC SttmntVerified" => ['1099', 'C&T: BUCC', 'SttmntVerified'],
			"1099 C&T: Holy Innocents Check Verified SttmntVerified" => ['1099', 'C&T: Holy Innocents', 'CHECK', 'Verified', 'SttmntVerified'],
			"1099 C&T: Other MintERR USAA-CHK Musik - Misc Gigs SttmntVerified" => ['1099', 'C&T: Other', 'MintERR', 'USAA-CHK', 'Musik - Misc Gigs', 'SttmntVerified'],
			"1099 C&T: Other MintERR USAA-CHK SttmntVerified" => ['1099', 'C&T: Other', 'MintERR', 'USAA-CHK', 'SttmntVerified'],
			"1099 C&T: Other SttmntVerified" => ['1099', 'C&T: Other', 'SttmntVerified'],
			"1099 C&T: St. Luke's Check Verified SttmntVerified" => ['1099', "C&T: St. Luke's", 'CHECK', 'Verified', 'SttmntVerified'],
			"1099 C&T: St. Luke's MintERR USAA-CHK SttmntVerified" => ['1099', "C&T: St. Luke's", 'MintERR', 'USAA-CHK', 'SttmntVerified'],
			"1099 C&T: St. Luke's MintERR USAA-SAV" => ['1099', "C&T: St. Luke's", 'MintERR', 'USAA-SAV'],
			"1099 C&T: St. Luke's SttmntVerified" => ['1099', "C&T: St. Luke's", 'SttmntVerified'],
			"1099 C&T: St. Luke's SttmntVerified Tax Year Issue" => ['1099', "C&T: St. Luke's", 'SttmntVerified', 'Tax Year Issue'],
			"1099 C&T: TBJ MintERR USAA-CHK SttmntVerified" => ['1099', 'C&T: TBJ', 'MintERR', 'USAA-CHK', 'SttmntVerified'],
			"1099 C&T: TBJ MintERR USAA-SAV" => ['1099', 'C&T: TBJ', 'MintERR', 'USAA-SAV'],
			"1099 C&T: TBJ SttmntVerified" => ['1099', 'C&T: TBJ', 'SttmntVerified'],
			"1099 Check Verified IT: EPI SttmntVerified" => ['1099', 'CHECK', 'Verified', 'IT: EPI', 'SttmntVerified'],
			"1099 Check Verified Musik - Misc Gigs SttmntVerified" => ['1099', 'CHECK', 'Verified', 'Musik - Misc Gigs', 'SttmntVerified'],
			"1099 IT: Birdhive/Misc MintERR USAA-SAV SttmntVerified" => ['1099', 'IT: Birdhive/Misc', 'MintERR', 'USAA-SAV', 'SttmntVerified'],
			"1099 IT: Birdhive/Misc SttmntVerified" => ['1099', 'IT: Birdhive/Misc', 'SttmntVerified'],
			"1099 IT: EPI MintERR USAA-CHK SttmntVerified" => ['1099', 'IT: EPI', 'MintERR', 'USAA-CHK', 'SttmntVerified'],
			"1099 IT: EPI SttmntVerified" => ['1099', 'IT: EPI', 'SttmntVerified'],
			"1099 IT: FFI MintERR USAA-CHK SttmntVerified" => ['1099', 'IT: FFI', 'MintERR', 'USAA-CHK', 'SttmntVerified'],
			"1099 IT: FFI MintERR USAA-SAV" => ['1099', 'IT: FFI', 'MintERR', 'USAA-SAV'],
			"1099 IT: FFI SttmntVerified Tax Year Issue" => ['1099', 'IT: FFI', 'SttmntVerified', 'Tax Year Issue'],
			"1099 IT: Harmony Shine SttmntVerified" => ['1099', 'IT: Harmony Shine', 'SttmntVerified'],
			"1099 MintERR USAA-CHK Musik - Misc Gigs SttmntVerified" => ['1099', 'MintERR', 'USAA-CHK', 'Musik - Misc Gigs', 'SttmntVerified'],
			"1099 MintERR USAA-SAV Musik - Misc Gigs SttmntVerified" => ['1099', 'MintERR', 'USAA-SAV', 'Musik - Misc Gigs', 'SttmntVerified'],
			"1099 Musik - Misc Gigs SttmntVerified" => ['1099', 'Musik - Misc Gigs', 'SttmntVerified'],
			"Acting - Misc Gigs SttmntVerified" => ['Acting - Misc Gigs', 'SttmntVerified'],
			"Acting - Misc Gigs SttmntVerified W2" => ['Acting - Misc Gigs', 'SttmntVerified', 'W2'],
			"Bonus C&T: TBJ MintERR USAA-CHK SttmntVerified" => ['Bonus', 'C&T: TBJ', 'MintERR', 'USAA-CHK', 'SttmntVerified'],
			"Bonus C&T: TBJ SttmntVerified" => ['Bonus', 'C&T: TBJ', 'SttmntVerified'],
			"C&T: 8th Church Check Verified SttmntVerified" => ['C&T: 8th Church', 'CHECK', 'Verified', 'SttmntVerified'],
			"C&T: 8th Church SttmntVerified" => ['C&T: 8th Church', 'SttmntVerified'],
			"C&T: BUCC" => ['C&T: BUCC'],
			"C&T: BUCC Chks FROM Misc Indv SttmntVerified" => ['C&T: BUCC', 'Chks FROM Misc Indv', 'SttmntVerified'],
			"C&T: BUCC SttmntVerified" => ['C&T: BUCC', 'SttmntVerified'],
			"C&T: Holy Innocents" => ['C&T: Holy Innocents'],
			"C&T: Holy Innocents Check Verified SttmntVerified" => ['C&T: Holy Innocents', 'CHECK', 'Verified', 'SttmntVerified'],
			"C&T: Holy Innocents Check Verified SttmntVerified Tax Year Issue" => ['C&T: Holy Innocents', 'CHECK', 'Verified', 'SttmntVerified', 'Tax Year Issue'],
			"C&T: Holy Innocents SttmntVerified" => ['C&T: Holy Innocents', 'SttmntVerified'],
			"C&T: Other Check Verified DIRECT DEPOSIT SttmntVerified W2" => ['C&T: Other', 'CHECK', 'Verified', 'DIRECT DEPOSIT', 'SttmntVerified', 'W2'],
			"C&T: Other Check Verified SttmntVerified W2" => ['C&T: Other', 'CHECK', 'Verified', 'SttmntVerified', 'W2'],
			"C&T: Other Chks FROM Misc Indv SttmntVerified" => ['C&T: Other', 'Chks FROM Misc Indv', 'SttmntVerified'],
			"C&T: Other SttmntVerified" => ['C&T: Other', 'SttmntVerified'],
			"C&T: Other SttmntVerified W2" => ['C&T: Other', 'SttmntVerified', 'W2'],
			"C&T: St. Luke's" => ["C&T: St. Luke's"],
			"C&T: St. Luke's CHECK" => ["C&T: St. Luke's", 'CHECK'],
			"C&T: St. Luke's SttmntVerified" => ["C&T: St. Luke's", 'SttmntVerified'],
			"C&T: TBJ" => ['C&T: TBJ'],
			"C&T: TBJ Check Verified DIRECT DEPOSIT MintERR USAA-CHK SttmntVerified W2" => ['C&T: TBJ', 'CHECK', 'Verified', 'DIRECT DEPOSIT', 'MintERR', 'USAA-CHK', 'SttmntVerified', 'W2'],
			"C&T: TBJ Check Verified DIRECT DEPOSIT SttmntVerified W2" => ['C&T: TBJ', 'CHECK', 'Verified', 'DIRECT DEPOSIT', 'SttmntVerified', 'W2'],
			"C&T: TBJ MintERR USAA-CHK SttmntVerified W2" => ['C&T: TBJ', 'MintERR', 'USAA-CHK', 'SttmntVerified', 'W2'],
			"C&T: TBJ SttmntVerified" => ['C&T: TBJ', 'SttmntVerified'],
			"C&T: TBJ SttmntVerified W2" => ['C&T: TBJ', 'SttmntVerified', 'W2'],
			"C&T: TBJ W2" => ['C&T: TBJ', 'W2'],
			"CASH transaction Deductible-Arts" => ['CASH transaction', 'Deductible-Arts'],
			"CHECK Chks TO Misc Indv SttmntVerified" => ['CHECK', 'Chks to Misc Indv', 'SttmntVerified'],
			"CHECK Deductible-Arts" => ['CHECK', 'Deductible-Arts'],
			"CHECK Deductible-Arts SttmntVerified" => ['CHECK', 'Deductible-Arts', 'SttmntVerified'],
			"CHECK Duplicate?" => ['CHECK', 'Duplicate?'],
			"CHECK IT: Birdhive/Misc Out of State SttmntVerified Tax Related" => ['CHECK', 'IT: Birdhive/Misc', 'Out of State', 'SttmntVerified', 'Tax Related'],
			"CHECK IT: EPI SttmntVerified Tax Related" => ['CHECK', 'IT: EPI', 'SttmntVerified', 'Tax Related'],
			"CHECK IT: EPI SttmntVerified Tax Related Tax Year Issue" => ['CHECK', 'IT: EPI', 'SttmntVerified', 'Tax Related', 'Tax Year Issue'],
			"CHECK MintERR USAA-CHK SttmntVerified" => ['CHECK', 'MintERR', 'USAA-CHK', 'SttmntVerified'],
			"CHECK Out of State SttmntVerified Tax Related" => ['CHECK', 'Out of State', 'SttmntVerified', 'Tax Related'],
			"CHECK SttmntVerified" => ['CHECK', 'SttmntVerified'],
			"CHECK SttmntVerified Tax Related" => ['CHECK', 'SttmntVerified', 'Tax Related'],
			"Check Verified Musik - Misc Gigs SttmntVerified" => ['CHECK', 'Verified', 'Musik - Misc Gigs', 'SttmntVerified'],
			"Chks FROM Misc Indv" => ['Chks FROM Misc Indv'],
			"Chks FROM Misc Indv IT: Birdhive/Misc SttmntVerified" => ['Chks FROM Misc Indv', 'IT: Birdhive/Misc', 'SttmntVerified'],
			"Chks FROM Misc Indv Musik - Misc Gigs SttmntVerified" => ['Chks FROM Misc Indv', 'Musik - Misc Gigs', 'SttmntVerified'],
			"Chks FROM Misc Indv SttmntVerified" => ['Chks FROM Misc Indv', 'SttmntVerified'],
			"Deductible-Arts OOT4B" => ['Deductible-Arts', 'OOT4B'],
			"Deductible-Arts OOT4B Out of Town" => ['Deductible-Arts', 'OOT4B', 'OOT'],
			"Deductible-Arts Out of State" => ['Deductible-Arts', 'Out of State'],
			"Deductible-Arts Out of Town" => ['Deductible-Arts', 'OOT'],
			"Deductible-Arts Out of Town Unverified" => ['Deductible-Arts', 'OOT', 'Unverified'],
			"Deductible-Arts ReceiptXcheck" => ['Deductible-Arts', 'Receipt X-Check'],
			"Deductible-Arts Tax Related" => ['Deductible-Arts', 'Tax Related'],
			"Deductible-Arts Unverified" => ['Deductible-Arts', 'Unverified'],
			"Deductible-IT Tax Related" => ['Deductible-IT', 'Tax Related'],
			"Deductible-IT Unverified" => ['Deductible-IT', 'Unverified'],
			"Direct Deposit" => ['DIRECT DEPOSIT'],
			"DOC MISSING" => ['DOC MISSING'],
			"Duplicate?" => ['Duplicate?'],
			"Duplicate? OOT4B" => ['Duplicate?', 'OOT4B'],
			"Events Attended" => ['Events Attended'],
			"Gifts Unverified" => ['Gifts', 'Unverified'],
			"Investments MNTERR MintERR USAA-CHK SttmntVerified" => ['Investments', 'MNTERR', 'MintERR', 'USAA-CHK', 'SttmntVerified'],
			"IT: Birdhive/Misc SttmntVerified" => ['IT: Birdhive/Misc', 'SttmntVerified'],
			"IT: EPI Split SttmntVerified" => ['IT: EPI', 'Split', 'SttmntVerified'],
			"IT: FFI" => ['IT: FFI'],
			"IT: RHT SttmntVerified" => ['IT: RHT', 'SttmntVerified'],
			"IT: RHT SttmntVerified W2" => ['IT: RHT', 'SttmntVerified', 'W2'],
			"Maybe Biz Deductible" => ['Maybe Biz Deductible'],
			"Maybe Duplicate? Fraudulent?" => ['Maybe Duplicate?', 'Fraudulent?'],
			"Maybe Duplicate? PayPal X-check" => ['Maybe Duplicate?', 'PayPal X-Check'],
			"Maybe Fraudulent" => ['Maybe Fraudulent'],
			"Maybe Fraudulent -- Unverified" => ['Maybe Fraudulent', 'Unverified'],
			"Maybe IT: EPI" => ['attn-rqrd', 'IT: EPI'],
			"MintERR Debit Card MintERR USAA-CHK SttmntVerified" => ['MintERR', 'Debit Card', 'MintERR', 'USAA-CHK', 'SttmntVerified'],
			"MintERR USAA-CHK Musik - Misc Gigs SttmntVerified" => ['MintERR', 'USAA-CHK', 'Musik - Misc Gigs', 'SttmntVerified'],
			"MintERR USAA-CHK SttmntVerified" => ['MintERR', 'USAA-CHK', 'SttmntVerified'],
			"MintERR USAA-SAV" => ['MintERR', 'USAA-SAV'],
			"MintERR USAA-SAV SttmntVerified" => ['MintERR', 'USAA-SAV', 'SttmntVerified'],
			"Musik - Misc Gigs SttmntVerified" => ['Musik - Misc Gigs', 'SttmntVerified'],
			"No Receipt" => ['No Receipt'],
			"No Receipt Total Includes Tip" => ['No Receipt', 'Total Includes Tip'],
			"Not Taxable" => ['Not Taxable'],
			"Not Taxable SttmntVerified" => ['Not Taxable', 'SttmntVerified'],
			"OOT4B Out of Town" => ['OOT4B', 'OOT'],
			"Out of Town" => ['OOT'],
			"Out of Town SttmntVerified" => ['OOT', 'SttmntVerified'],
			"PayPal PayPal X-check" => ['PayPal', 'PayPal X-Check'],
			"PayPal PayPal X-check SttmntVerified" => ['PayPal', 'PayPal X-Check', 'SttmntVerified'],
			"PayPal SttmntVerified" => ['PayPal', 'SttmntVerified'],
			"PayPal X-check" => ['PayPal X-Check'],
			"Professional Development" => ['Professional Development'],
			"Split SttmntVerified" => ['Split', 'SttmntVerified'],
			"Spreadsheet Checked" => ['Spreadsheet Checked'],
			"SpreadsheetChecked SttmntVerified" => ['Spreadsheet Checked', 'SttmntVerified'],
			"Total DN Include Tip" => ['Total DN Include Tip'],
			"Total Includes Tip" => ['Total Includes Tip'],
			"X-check OK" => ['X-Check OK'],
            
            // You can organize them by category if it helps:
            
            // === 1099 Related ===
            // '1099 Other Tag' => ['1099', 'Other', 'Tag'],
            
            // === IT Related ===
            // 'IT: FFI Another' => ['IT: FFI', 'Another'],
            
            // === Statement Related ===
            // 'SttmntVerified Other' => ['SttmntVerified', 'Other'],
            
            // Continue adding your mappings...
        ];
    }
    
    /**
     * Helper method: Convert a spreadsheet paste into the format above
     * 
     * If you have your mappings in a spreadsheet with columns like:
     * Column A: Combo Tag
     * Column B: Tag 1
     * Column C: Tag 2
     * Column D: Tag 3
     * 
     * You can paste TSV (tab-separated) data here and it will convert it.
     * 
     * Usage:
     * 1. Copy cells from your spreadsheet
     * 2. Paste between the quotes in $tsvData below
     * 3. Call this method once to get formatted output
     * 4. Copy the output into getMappings() above
     */
    public static function convertTsvToMapping(string $tsvData): array
    {
        $lines = explode("\n", trim($tsvData));
        $mappings = [];
        
        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) < 2) {
                continue; // Skip invalid lines
            }
            
            $comboTag = trim($parts[0]);
            $separateTags = array_filter(array_map('trim', array_slice($parts, 1)));
            
            if ($comboTag && !empty($separateTags)) {
                $mappings[$comboTag] = $separateTags;
            }
        }
        
        return $mappings;
    }
    
    /**
     * Helper method: Import from CSV file
     * 
     * CSV format:
     * "Combo Tag","Tag 1","Tag 2","Tag 3"
     * "1099 IT: FFI SttmntVerified","1099","IT: FFI","SttmntVerified"
     * 
     * @param string $filePath Path to CSV file
     * @return array<string, string[]>
     */
    public static function importFromCsv(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }
        
        $mappings = [];
        $handle = fopen($filePath, 'r');
        
        // Skip header row if present
        $firstRow = fgetcsv($handle);
        if ($firstRow && strtolower($firstRow[0]) === 'combo tag') {
            // This was a header, continue to next row
        } else {
            // This was data, process it
            if ($firstRow && count($firstRow) >= 2) {
                $comboTag = trim($firstRow[0]);
                $separateTags = array_filter(array_map('trim', array_slice($firstRow, 1)));
                if ($comboTag && !empty($separateTags)) {
                    $mappings[$comboTag] = $separateTags;
                }
            }
        }
        
        // Process remaining rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                continue;
            }
            
            $comboTag = trim($row[0]);
            $separateTags = array_filter(array_map('trim', array_slice($row, 1)));
            
            if ($comboTag && !empty($separateTags)) {
                $mappings[$comboTag] = $separateTags;
            }
        }
        
        fclose($handle);
        return $mappings;
    }
    
    /**
     * Helper method: Generate PHP code from array
     * 
     * Takes an array of mappings and outputs formatted PHP code
     * that you can copy into getMappings()
     */
    public static function generatePhpCode(array $mappings): string
    {
        $code = "[\n";
        
        foreach ($mappings as $comboTag => $separateTags) {
            $tagsStr = "'" . implode("', '", array_map('addslashes', $separateTags)) . "'";
            $code .= "    '" . addslashes($comboTag) . "' => [" . $tagsStr . "],\n";
        }
        
        $code .= "]";
        
        return $code;
    }
}
