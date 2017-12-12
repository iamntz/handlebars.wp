<?php

/**
 * WP Post Wrapper
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */

namespace iamntz\handlebarsWP\utils;

class Post
{
	private $post;

	private $_excerptReadMore;
	private $_excerptLength;

	public function __construct($postID = null)
	{
		$this->postID = $postID;
		$this->post = get_post($this->postID, 'OBJECT', 'display');

		$this->post->content = apply_filters('the_content', $this->post->post_content);
		$this->post->content = str_replace(']]>', ']]&gt;', $this->post->content);

		$this->post->title = get_the_title($this->post);
		$this->post->permalink = get_permalink($this->post);

		$this->post = apply_filters('iamntz/wp/post', $this->post);
	}

	public function get()
	{
		return json_decode(json_encode($this->post), true);
	}

	public function withExcerpt($excerptLength = false, $readMore = '')
	{
		$this->_excerptReadMore = $readMore;
		$this->_excerptLength = $excerptLength;

		add_filter( 'excerpt_more', [ $this, '_excerptReadMore' ], 20 );
		if ($this->_excerptLength) {
			add_filter( 'excerpt_length', [ $this, '_excerptLength' ], 20 );
		}

		$this->post->excerpt = apply_filters('the_excerpt', get_the_excerpt());

		remove_filter( 'excerpt_length', [ $this, '_excerptLength' ], 20 );
		remove_filter( 'excerpt_more', [ $this, '_excerptReadMore' ], 20 );

		return $this;
	}

	public function _excerptReadMore()
	{
		return $this->_excerptReadMore;
	}


	public function _excerptLength()
	{
		return $this->_excerptLength;
	}

	public function withDate($format = null)
	{
		$this->post->date = apply_filters('iamntz/wp/post-date', [
			'iso' => get_the_date('c', $this->post),
			'display' => get_the_date($format, $this->post),
		], $this->post);

		return $this;
	}

	public function withThumbnail($size = 'thumbnail', $className = '')
	{
		$thumbID = get_post_thumbnail_id($this->post);

		$thumbSrc = wp_get_attachment_image_src($thumbID, $size);

		if (empty($thumbSrc[0])) {
			return $this;
		}

		$attrs = [
			'class' => $className
		];

		$thumb = [
			'raw' => [
				'src' => $thumbSrc[0],
				'w' => $thumbSrc[1],
				'h' => $thumbSrc[2],
			],
			'html' => wp_get_attachment_image($thumbID, $size, false, $attrs),
		];

		$this->post->thumbnail = apply_filters('iamntz/wp/post-thumbnail', $thumb, $this->post);

		return $this;
	}

	public function withPostClass($postClass = '')
	{
		$postClass = apply_filters('iamntz/wp/post-class', $postClass, $this->post);
		$this->post->post_class = implode(' ', get_post_class($postClass, $this->post));

		return $this;
	}

	public function withAuthor()
	{
		$author = get_userdata($this->post->post_author);

		foreach ($author->data as $key => $data) {
			if (in_array($key, ['user_pass'])) {
				continue;
			}

			$cleanAuthor[$key] = apply_filters("get_the_author_{$key}", $data, $this->post->post_author, $this->post->post_author);
		}

		$cleanAuthor['display_name'] = $author->data->display_name;
		$cleanAuthor['permalink'] = get_author_posts_url($author->ID, $cleanAuthor['user_nicename']);
		$cleanAuthor['description'] = get_user_meta($author->ID, 'description', true);

		$this->post->author = apply_filters('iamntz/wp/post-author', $cleanAuthor, $this->post);

		return $this;
	}

	public function withAuthorMeta($key, $single = true)
	{
		if (empty($this->post->author)) {
			$this->withAuthor();
		}

		if (is_string($key)) {
			$this->_setAuthorMeta($key, $single);
		} elseif (is_array($key)) {
			array_walk($key, [$this, '_setAuthorMeta']);
		}

		return $this;
	}

	public function _setAuthorMeta($key, $single = true)
	{
		$meta = empty($this->post->author['meta']) ? [] : $this->post->author['meta'];

		if (empty($meta[$key])) {
			$meta[$key] = get_user_meta($this->post->author['ID'], $key, $single);
		}

		$this->post->author['meta'] = $meta;
	}

	public function withTerms($taxonomy, $args = [])
	{
		$postTerms = wp_get_post_terms($this->postID, $taxonomy, $args);
		$postTerms = array_map(function ($term) {
			$term->permalink = get_term_link($term);
			return $term;
		}, $postTerms);

		$terms = empty($this->post->terms) ? [] : $this->post->terms;
		$terms[$taxonomy] = $postTerms;

		$this->post->terms = apply_filters('iamntz/wp/post-terms', $terms, $this->post);

		return $this;
	}
}
