<?php

namespace atc\Bkkp\Modules\Employment\PostTypes;

use atc\WHx4\Core\PostTypeHandler;

class Employer extends PostTypeHandler
{
	public function __construct(WP_Post|null $post = null) {
		$config = [
			'slug'        => 'employer',
			//'plural_slug' => 'monsters',
			'labels'      => [
				//'add_new_item' => 'Summon New Monster',
				//'not_found'    => 'No monsters lurking nearby',
			],
			//'menu_icon'   => 'dashicons-palmtree',
		];

		parent::__construct( $config, $post );
	}

	public function boot(): void
	{
	    parent::boot(); // Optional if you add shared logic later

		/*$this->applyTitleArgs( $this->getSlug(), [
			'line_breaks'    => true,
			'show_subtitle'  => true,
			'hlevel_sub'     => 4,
			'called_by'      => 'Employer::boot',
			'append'         => ' {Rowarrr!}',
		]);*/
	}

    /*
    // Usage in code:
    $handler = new Employer( get_post(123) );
	if ($handler->isPost()) {
		$title = get_the_title( $handler->getObject() );
	}
	*/

	public function getCPTContent() {
		return "hello";
	}

    public function get_color() {
        // Assuming the color is stored as a custom field, for example, _employer_color
        return isset($this->post) ? get_post_meta($this->post->ID, '_employer_color', true) : null;
    }

    // Other methods related to the Employer...
}

