<?php
/**
 * The API functionality of the plugin.
 *
 * @since      1.0.0
 */
class Varabit_SEO_Audit_API {

    /**
     * Google PageSpeed Insights API key.
     *
     * @var string
     */
    private $pagespeed_api_key = ''; // You should set this in the WordPress admin

    /**
     * Get PageSpeed Insights data for a URL.
     *
     * @param string $url The URL to analyze.
     * @return array PageSpeed Insights results.
     */
    public function get_pagespeed_insights($url) {
        $results = array(
            'desktop' => array(),
            'mobile' => array(),
            'errors' => array(),
        );
        
        // Get PageSpeed data for desktop
        $desktop_data = $this->fetch_pagespeed_data($url, 'desktop');
        if (is_wp_error($desktop_data)) {
            $results['errors'][] = array(
                'type' => 'error',
                'message' => 'Failed to fetch desktop PageSpeed data: ' . $desktop_data->get_error_message(),
            );
        } else {
            $results['desktop'] = $this->parse_pagespeed_data($desktop_data);
        }
        
        // Get PageSpeed data for mobile
        $mobile_data = $this->fetch_pagespeed_data($url, 'mobile');
        if (is_wp_error($mobile_data)) {
            $results['errors'][] = array(
                'type' => 'error',
                'message' => 'Failed to fetch mobile PageSpeed data: ' . $mobile_data->get_error_message(),
            );
        } else {
            $results['mobile'] = $this->parse_pagespeed_data($mobile_data);
        }
        
        return $results;
    }
    
    /**
     * Fetch PageSpeed Insights data from Google API.
     *
     * @param string $url The URL to analyze.
     * @param string $strategy The strategy (desktop or mobile).
     * @return array|WP_Error PageSpeed data or error.
     */
    private function fetch_pagespeed_data($url, $strategy = 'desktop') {
        $api_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
        $args = array(
            'url' => $url,
            'strategy' => $strategy,
        );
        
        // Add API key if available
        if (!empty($this->pagespeed_api_key)) {
            $args['key'] = $this->pagespeed_api_key;
        }
        
        $request_url = add_query_arg($args, $api_url);
        
        $response = wp_remote_get($request_url, array(
            'timeout' => 30, // Increased timeout for PageSpeed API
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data)) {
            return new WP_Error('invalid_response', 'Invalid response from PageSpeed API');
        }
        
        return $data;
    }
    
    /**
     * Parse PageSpeed Insights data.
     *
     * @param array $data Raw PageSpeed data.
     * @return array Parsed PageSpeed data.
     */
    private function parse_pagespeed_data($data) {
        $result = array(
            'score' => 0,
            'metrics' => array(),
            'opportunities' => array(),
        );
        
        // Get overall score
        if (isset($data['lighthouseResult']['categories']['performance']['score'])) {
            $result['score'] = $data['lighthouseResult']['categories']['performance']['score'];
        }
        
        // Get metrics
        if (isset($data['lighthouseResult']['audits'])) {
            $audits = $data['lighthouseResult']['audits'];
            
            // Core Web Vitals
            $core_metrics = array(
                'first-contentful-paint' => 'First Contentful Paint',
                'speed-index' => 'Speed Index',
                'largest-contentful-paint' => 'Largest Contentful Paint',
                'interactive' => 'Time to Interactive',
                'total-blocking-time' => 'Total Blocking Time',
                'cumulative-layout-shift' => 'Cumulative Layout Shift',
            );
            
            foreach ($core_metrics as $metric_key => $metric_name) {
                if (isset($audits[$metric_key])) {
                    $metric = $audits[$metric_key];
                    $result['metrics'][$metric_key] = array(
                        'name' => $metric_name,
                        'score' => $metric['score'],
                        'value' => isset($metric['displayValue']) ? $metric['displayValue'] : '',
                        'description' => isset($metric['description']) ? $metric['description'] : '',
                    );
                }
            }
            
            // Improvement opportunities
            foreach ($audits as $audit_key => $audit) {
                if (isset($audit['details']['type']) && $audit['details']['type'] === 'opportunity' && $audit['score'] < 1) {
                    $result['opportunities'][] = array(
                        'name' => $audit['title'],
                        'description' => $audit['description'],
                        'score' => $audit['score'],
                    );
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Analyze website content for SEO factors.
     *
     * @param string $url The URL to analyze.
     * @return array Analysis results.
     */
    public function analyze_website_content($url) {
        $results = array(
            'meta_tags' => array(),
            'headings' => array(),
            'images' => array(),
            'keywords' => array(),
            'errors' => array(),
        );
        
        // Fetch the website content
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'user-agent' => 'Varabit SEO Audit Tool/1.0',
        ));
        
        if (is_wp_error($response)) {
            $results['errors'][] = array(
                'type' => 'error',
                'message' => 'Failed to fetch website content: ' . $response->get_error_message(),
            );
            return $results;
        }
        
        $body = wp_remote_retrieve_body($response);
        
        if (empty($body)) {
            $results['errors'][] = array(
                'type' => 'error',
                'message' => 'Empty response from website.',
            );
            return $results;
        }
        
        // Create a DOMDocument for parsing HTML
        $dom = new DOMDocument();
        
        // Suppress warnings from malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML($body);
        libxml_clear_errors();
        
        // Create XPath object
        $xpath = new DOMXPath($dom);
        
        // Analyze meta tags
        $results['meta_tags'] = $this->analyze_meta_tags($xpath);
        
        // Analyze headings
        $results['headings'] = $this->analyze_headings($xpath);
        
        // Analyze images
        $results['images'] = $this->analyze_images($xpath);
        
        // Analyze keywords
        $results['keywords'] = $this->analyze_keywords($body, $results['meta_tags']);
        
        return $results;
    }
    
    /**
     * Analyze meta tags.
     *
     * @param DOMXPath $xpath XPath object for the page.
     * @return array Meta tag analysis results.
     */
    private function analyze_meta_tags($xpath) {
        $meta_tags = array(
            'title' => '',
            'description' => '',
            'robots' => '',
            'canonical' => '',
            'issues' => array(),
        );
        
        // Get title
        $title_nodes = $xpath->query('//title');
        if ($title_nodes->length > 0) {
            $meta_tags['title'] = trim($title_nodes->item(0)->nodeValue);
            
            // Check title length
            $title_length = mb_strlen($meta_tags['title']);
            if ($title_length < 30) {
                $meta_tags['issues'][] = array(
                    'type' => 'warning',
                    'message' => 'Title tag is too short (' . $title_length . ' characters). Recommended length is 50-60 characters.',
                );
            } elseif ($title_length > 60) {
                $meta_tags['issues'][] = array(
                    'type' => 'warning',
                    'message' => 'Title tag is too long (' . $title_length . ' characters). Recommended length is 50-60 characters.',
                );
            }
        } else {
            $meta_tags['issues'][] = array(
                'type' => 'error',
                'message' => 'No title tag found.',
            );
        }
        
        // Get meta description
        $description_nodes = $xpath->query('//meta[@name="description"]');
        if ($description_nodes->length > 0) {
            $meta_tags['description'] = trim($description_nodes->item(0)->getAttribute('content'));
            
            // Check description length
            $desc_length = mb_strlen($meta_tags['description']);
            if ($desc_length < 120) {
                $meta_tags['issues'][] = array(
                    'type' => 'warning',
                    'message' => 'Meta description is too short (' . $desc_length . ' characters). Recommended length is 150-160 characters.',
                );
            } elseif ($desc_length > 160) {
                $meta_tags['issues'][] = array(
                    'type' => 'warning',
                    'message' => 'Meta description is too long (' . $desc_length . ' characters). Recommended length is 150-160 characters.',
                );
            }
        } else {
            $meta_tags['issues'][] = array(
                'type' => 'error',
                'message' => 'No meta description found.',
            );
        }
        
        // Get robots meta
        $robots_nodes = $xpath->query('//meta[@name="robots"]');
        if ($robots_nodes->length > 0) {
            $meta_tags['robots'] = trim($robots_nodes->item(0)->getAttribute('content'));
            
            // Check for noindex
            if (strpos($meta_tags['robots'], 'noindex') !== false) {
                $meta_tags['issues'][] = array(
                    'type' => 'warning',
                    'message' => 'Page is set to noindex, which prevents search engines from indexing it.',
                );
            }
        }
        
        // Get canonical URL
        $canonical_nodes = $xpath->query('//link[@rel="canonical"]');
        if ($canonical_nodes->length > 0) {
            $meta_tags['canonical'] = trim($canonical_nodes->item(0)->getAttribute('href'));
        }
        
        return $meta_tags;
    }
    
    /**
     * Analyze headings structure.
     *
     * @param DOMXPath $xpath XPath object for the page.
     * @return array Headings analysis results.
     */
    private function analyze_headings($xpath) {
        $headings = array(
            'h1' => array(),
            'h2' => array(),
            'h3' => array(),
            'h4' => array(),
            'h5' => array(),
            'h6' => array(),
            'issues' => array(),
        );
        
        // Get all headings
        for ($i = 1; $i <= 6; $i++) {
            $heading_nodes = $xpath->query('//h' . $i);
            foreach ($heading_nodes as $node) {
                $headings['h' . $i][] = trim($node->nodeValue);
            }
        }
        
        // Check for H1
        if (count($headings['h1']) === 0) {
            $headings['issues'][] = array(
                'type' => 'error',
                'message' => 'No H1 heading found. Each page should have exactly one H1 heading.',
            );
        } elseif (count($headings['h1']) > 1) {
            $headings['issues'][] = array(
                'type' => 'warning',
                'message' => 'Multiple H1 headings found (' . count($headings['h1']) . '). Each page should have exactly one H1 heading.',
            );
        }
        
        // Check heading hierarchy
        if (count($headings['h1']) === 0 && count($headings['h2']) > 0) {
            $headings['issues'][] = array(
                'type' => 'warning',
                'message' => 'H2 headings found without an H1 heading. Maintain proper heading hierarchy.',
            );
        }
        
        return $headings;
    }
    
    /**
     * Analyze images for alt text.
     *
     * @param DOMXPath $xpath XPath object for the page.
     * @return array Image analysis results.
     */
    private function analyze_images($xpath) {
        $images = array();
        
        // Get all images
        $img_nodes = $xpath->query('//img');
        foreach ($img_nodes as $node) {
            $src = $node->getAttribute('src');
            if (!empty($src)) {
                $images[] = array(
                    'src' => $src,
                    'alt' => $node->getAttribute('alt'),
                    'has_alt' => $node->hasAttribute('alt'),
                );
            }
        }
        
        return $images;
    }
    
    /**
     * Analyze keywords from content.
     *
     * @param string $content The page content.
     * @param array $meta_tags Meta tags data.
     * @return array Keyword analysis results.
     */
    private function analyze_keywords($content, $meta_tags) {
        $keywords = array(
            'top_keywords' => array(),
            'density' => array(),
            'issues' => array(),
        );
        
        // Strip HTML tags
        $text = strip_tags($content);
        
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove special characters
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        
        // Get all words
        $words = str_word_count($text, 1);
        
        // Count word frequency
        $word_count = array_count_values($words);
        
        // Remove common words
        $common_words = array('the', 'and', 'a', 'to', 'of', 'in', 'is', 'that', 'it', 'with', 'for', 'as', 'on', 'was', 'be', 'at', 'this', 'by', 'are', 'or', 'an', 'but', 'not', 'from');
        foreach ($common_words as $word) {
            unset($word_count[$word]);
        }
        
        // Sort by frequency
        arsort($word_count);
        
        // Get top 10 keywords
        $keywords['top_keywords'] = array_slice($word_count, 0, 10, true);
        
        // Calculate keyword density
        $total_words = count($words);
        foreach ($keywords['top_keywords'] as $word => $count) {
            $keywords['density'][$word] = round(($count / $total_words) * 100, 2);
        }
        
        // Check if top keywords are in title and description
        if (!empty($meta_tags['title']) && !empty($keywords['top_keywords'])) {
            $title_lower = strtolower($meta_tags['title']);
            $found = false;
            
            foreach (array_keys(array_slice($keywords['top_keywords'], 0, 3, true)) as $top_word) {
                if (strpos($title_lower, $top_word) !== false) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $keywords['issues'][] = array(
                    'type' => 'warning',
                    'message' => 'None of the top 3 keywords found in the page title.',
                );
            }
        }
        
        return $keywords;
    }
}