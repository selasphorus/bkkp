<?php

declare(strict_types=1);

namespace atc\Bkkp\Modules\Employment\Shortcodes;

use atc\WHx4\Core\WHx4;
use atc\WHx4\Utils\ClassInfo;
use atc\WHx4\Core\PostTypeHandler;
use atc\WHx4\Core\ViewLoader;
use atc\WHx4\Core\SubtypeRegistry;
use atc\WHx4\Core\Contracts\ShortcodeInterface;

final class EmploymentIncomeShortcode implements ShortcodeInterface
{
    // Adjust to your actual CPT slug (e.g., 'whx4_event' or 'event').
    //private const CPT = 'group';

    // This is the tag by which the shortcode will be called
    public static function tag(): string
    {
        return 'employment_income';
    }
    
    public function render(array $atts = [], string $content = '', string $tag = ''): string
    {
        $info = "";
        
        $ctx = WHx4::ctx();
        $key = ClassInfo::getModuleKey(self::class);
        $module = $ctx->getModule($key);
        if (!$module) {
            return '<p>Employment module inactive.</p>';
        }

        //$stats = $module->getModuleStats(); // module-level method
        // WIP 10/04/25 -- allow single year or range; default to current year
        //if ( isset($atts['year']) ) { $year = $atts['year']; } else { $year = date('Y'); }
        if ( isset($atts['scope']) ) { $scope = $atts['scope']; } else { $scope = date('Y'); }
        $employers = $module->findEmployers($scope) ?? [];
        $employerPosts  = $employers['posts'] ?? [];

        // Pagination info for the view.
        $pagination = $employers['pagination'] ?? ['found' => 0, 'max_pages' => 0, 'paged' => 1];

        // Troubleshooting info
        $info .= "[" . $employers['pagination']['found'] . "] posts found<br />";
        if ( $employers['pagination']['found'] == 0 ) {
            $info .= "findEmployers result: <pre>". print_r($employers, true) . "</pre>";
        }

        // Handler factory so views can call CPT methods safely.
        $handlerFactory = [PostTypeHandler::class, 'getHandlerForPost'];
        
        // Set the view
        $view = "employment-income"; //$view = "module-view-test";
        
        $vars = [
            'posts'      => $employerPosts,
            'handler'    => $handlerFactory,
            //'atts'       => $atts,
            'pagination' => $pagination,
            //'stats' => $stats,
            'info' => $info, // for TS -- deprecate in favor of:
            // Optionally pass debug through when WHX4_DEBUG is on:
            'debug'      => $employers['debug'] ?? null,
        ];

        return ViewLoader::renderToString(
            $view,
            $vars,
            ['kind' => 'partial', 'module' => 'employment'] //, 'post_type' => self::CPT
        );
    }
    
    /*
    // V1 -- not functional but keeping as WIP re Subtypes
    public function render(array $atts = [], string $content = '', string $tag = ''): string
    {
        $info = "";
        
        // 1) Resolve subtype instance
		$subtype = SubtypeRegistry::resolve(self::CPT, 'employers');
		if (!$subtype) {
			// Graceful failure: no subtype registered
			return '';
		}

        // Merge with canonical defaults from the CPT handler (parent-powered).
        $atts = shortcode_atts(PostTypeHandler::queryDefaults(), $atts, $tag);
        //$atts['date_meta']['key'] ='whx4_events_start_date'; // tft

        // Run the unified query pipeline.
        $result = $subType::find($atts);
        //$result = $subType->find($atts);
        $posts  = $result['posts'] ?? [];

        // Pagination info for the view.
        $pagination = $result['pagination'] ?? ['found' => 0, 'max_pages' => 0, 'paged' => 1];

        // Troubleshooting info
        $info .= "[" . $result['pagination']['found'] . "] posts found<br />";
        //$info .= "posts: <pre>" . print_r($posts, true) . "</pre>";
        //$info .= "atts: <pre>" . print_r($atts, true) . "</pre>";
        //$info .= "wp_args: <pre>" . print_r($result['debug']['args'], true) . "</pre>";
        //$info .= "query_request: <pre>" . $result['debug']['query_request'] . "</pre>";

        // Handler factory so views can call CPT methods safely.
        $handlerFactory = [PostTypeHandler::class, 'getHandlerForPost'];

        // Choose a view variant (list|grid|table); fall back to list.
        $viewVariant = in_array($atts['view'], ['list', 'grid', 'table'], true) ? $atts['view'] : 'list';
        $view = $viewVariant;

        $vars = [
            'posts'      => $posts,
            'handler'    => $handlerFactory,
            'atts'       => $atts,
            'pagination' => $pagination,
            'info' => $info, // for TS -- deprecate in favor of:
            // Optionally pass debug through when WHX4_DEBUG is on:
            'debug'      => $result['debug'] ?? null,
        ];

        return ViewLoader::renderToString(
            $view,
            $vars,
            ['kind' => 'partial', 'module' => 'employment', 'post_type' => self::CPT]
        );
    }
    */
}
