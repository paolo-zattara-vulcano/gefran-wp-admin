<?php

if (!(defined('WP_CLI') && WP_CLI)) {
    return;
}

class Algolia_Command
{
    public function reindex_post_atomic($args, $assoc_args)
    {
        global $algolia;

        $type = isset($assoc_args['type']) ? $assoc_args['type'] : 'post';

        $index = $algolia->client->initIndex(
            apply_filters('algolia_index_name', $type)
        );

        $queryArgs = [
            'posts_per_page' => 100,
            'post_status' => 'publish',
        ];

        $iterator = new Algolia_Post_Iterator($type, $queryArgs);

        $index->replaceAllObjects($iterator);

        WP_CLI::success("Reindexed $type posts in Algolia");
    }
}

class Algolia_Post_Iterator implements Iterator
{
    /**
     * @var array
     */
    private $queryArgs;

    private $key;

    private $paged;

    private $posts;
    private $type;

    public function __construct($type, array $queryArgs = [])
    {
        $this->type = $type;
        $this->queryArgs = ['post_type' => $type] + $queryArgs;
    }

    public function current()
    {
        return $this->serialize($this->posts[$this->key]);
    }

    public function next()
    {
        $this->key++;
    }

    public function key()
    {
        $this->key;
    }

    public function valid()
    {
        if (isset($this->posts[$this->key])) {
            return true;
        }

        $this->paged++;
        $query = new WP_Query(['paged' => $this->paged] + $this->queryArgs);

        if (!$query->have_posts()) {
            return false;
        }

        $this->posts = $query->posts;
        $this->key = 0;

        return true;
    }

    public function rewind()
    {
        $this->key = 0;
        $this->paged = 0;
        $this->posts = [];
    }

    private function serialize(WP_Post $post)
    {
        $record = (array) apply_filters($this->type . '_to_record', $post);

        if (!isset($record['objectID'])) {
            $record['objectID'] = implode('#', [$post->post_type, $post->ID]);
        }

        return $record;
    }
}


WP_CLI::add_command('algolia', 'Algolia_Command');
