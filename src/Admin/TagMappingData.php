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
