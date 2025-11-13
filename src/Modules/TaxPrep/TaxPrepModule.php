<?php

namespace atc\Bkkp\Modules\TaxPrep;

use atc\BhWP\Core\Module as BaseModule;

// Post Types
use atc\Bkkp\Modules\TaxPrep\PostTypes\TaxForm;
use atc\Bkkp\Modules\TaxPrep\PostTypes\TaxPayment;
// TODO: create separate CPT as equiv to annual Finances XLSX files? Or Documents Subtype?

// Taxonomies
use atc\Bkkp\Modules\TaxPrep\Taxonomies\IncomeCategory;
use atc\Bkkp\Modules\TaxPrep\Taxonomies\TaxCategory;
use atc\Bkkp\Modules\TaxPrep\Taxonomies\ItemLabel; // Shared taxonomy -- put this elsewhere?

// Define the module class
final class TaxPrepModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();
        parent::boot();

        add_filter( 'whx4_register_subtypes', function( array $providers ): array {
            $providers[] = new \atc\Bkkp\Modules\TaxPrep\Subtypes\TaxDocumentsSubtype(); // TODO: add use statement above to simplify this line?
            //$providers[] = new \atc\Bkkp\Modules\TaxPrep\Subtypes\WorkPaymentsSubtype();
            return $providers;
        } );

        // TODO: change this so that taxonomies are auto-detected, like field groups
        add_filter('whx4_register_taxonomy_handlers', function (array $handlers): array {
            $handlers['income_category'] = IncomeCategory::class;
            $handlers['tax_category'] = TaxCategory::class;
            return $handlers;
        });

        add_filter('whx4_register_shared_taxonomy_handlers', function(array $handlers): array {
            $handlers[] = ItemLabel::class;
            return $handlers;
        });
        
        add_action('acf/save_post', array($this, 'calculate_total_withheld'), 20);
        add_action('acf/input/admin_enqueue_scripts',  array($this, 'enqueue_acf_calculation_script')); // TBC whether this should be handled differently
    }

    public function getPostTypeHandlerClasses(): array
    {
        return [
            TaxForm::class,
            TaxPayment::class, // temporary
            //ZZZ::class,
        ];
    }
    
	function calculate_total_withheld($post_id) {
		// Avoid infinite loops
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		
		// Define the fields you want to sum
		$fields_to_sum = array(
			'fed_tax_withheld',
			'ss_tax_withheld',
			'medicare_tax_withheld',
			'state_tax_withheld',
			'local_income_tax', //'local_tax_withheld',
			'other_tax'
		);
		
		$total = 0;
		foreach ($fields_to_sum as $field_name) {
			$value = get_field($field_name, $post_id);
			$total += floatval($value);
		}
		
		// Update the calculated field
		update_field('total_withheld', $total, $post_id);
	}
	
	// TBD where this actually belongs -- probably not here
	function enqueue_acf_calculation_script() {
		?>
		<script type="text/javascript">
		(function($) {
			// Field names to sum (use the ACF field keys for reliability)
			var fieldsToSum = [
				'field_xxxxx1', // Replace with your actual field keys
				'field_xxxxx2',
				'field_xxxxx3'
			];
			
			function calculateTotal() {
				var total = 0;
				fieldsToSum.forEach(function(fieldKey) {
					var value = $('[data-key="' + fieldKey + '"] input').val();
					total += parseFloat(value) || 0;
				});
				
				$('[data-key="field_642c9b4848d55"] input').val(total.toFixed(2));
			}
			
			// Calculate on page load
			acf.addAction('ready', calculateTotal);
			
			// Recalculate when any source field changes
			fieldsToSum.forEach(function(fieldKey) {
				acf.addAction('change', function() {
					calculateTotal();
				});
			});
			
		})(jQuery);
		</script>
		<?php
	}
}
