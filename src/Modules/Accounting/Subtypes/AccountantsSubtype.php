<?php

namespace atc\Bkkp\Modules\Accounting\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;
use atc\WHx4\Core\Traits\SubtypeDefaults;
use atc\WHx4\Core\Traits\SubtypeQueryHelpers;

final class AccountantsSubtype implements SubtypeInterface
{
    use SubtypeDefaults, SubtypeQueryHelpers;
    
    public const POST_TYPE = 'person';
    public const TAXONOMY = 'people_category';
    public const TERM = 'accountant';

    public function getPostType(): string
    {
        return self::POST_TYPE;
    }

    public function getSlug(): string
    {
        return 'accountants';
    }

    public function getLabel(): string
    {
        return 'Accountants';
    }
    
    public function getTaxonomy(): string
	{
		return self::TAXONOMY;
	}

    public function getTermArgs(): array
    {
        return []; // e.g. ['description' => 'Organizations that employ people']
    }
    
    // Recommended to keep UI label ("Employers") separate from the actual term slug ("employer")
	public function getTermSlug(): string
	{
		//return 'employer';
		return self::TERM; // singular term slug (internal taxonomy term)
	}
	
	/**
	 * Return a PostQuery spec for this subtype.
	 */
	public function getQuerySpec(array $overrides = []): array
	{
		$spec = [
			'post_type' => self::POST_TYPE,
			'tax'       => [
				self::TAXONOMY => [$this->getTermSlug()],
			],
			'per_page'  => 20,
			'orderby'   => 'title',
			'order'     => 'ASC',
		];
	
		return array_replace_recursive($spec, $overrides);
	}
	
	/**
	 * Convenience: run the query and return WP_Post[].
	 */
	public function find(array $overrides = []): array
	{
		return PostQuery::fetch(
			$this->getQuerySpec($overrides)
		);
	}
}
