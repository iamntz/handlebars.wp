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

	public function __construct($postID = null, $withContent = true)
	{
		$this->postID = $postID;
		$this->post = get_post($this->postID, 'OBJECT', 'display');

		$this->post->thumbnails = [];

		if ($withContent) {
			$this->post->content = WP::get()->buffer_the_content();
			$this->post->post_content = $this->post->content;
		}

		$this->post->title = get_the_title($this->post);
		$this->post->permalink = get_permalink($this->post);
	}

	public function get($asObject = false)
	{
		$this->post = apply_filters('iamntz/wp/post', $this->post);
		$this->post = apply_filters("iamntz/wp/post/post-type={$this->post->post_type}", $this->post);

		if ($asObject) {
			return $this->post;
		}

		return json_decode(json_encode($this->post), true);
	}

	public function withExcerpt($excerptLength = false, $readMore = '')
	{
		$this->_excerptReadMore = $readMore;
		$this->_excerptLength = $excerptLength;

		add_filter('excerpt_more', [$this, '_excerptReadMore'], 20);
		if ($this->_excerptLength) {
			add_filter('excerpt_length', [$this, '_excerptLength'], 20);
		}

		$excerpt = !empty($this->post->post_excerpt) ? $this->post->post_excerpt : $this->post->post_content;

		$excerpt = strip_shortcodes( $excerpt );

		$excerpt = apply_filters( 'the_content', $excerpt );
		$excerpt = str_replace(']]>', ']]&gt;', $excerpt);

		$excerpt_length = apply_filters( 'excerpt_length', 55 );
		$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );

		$excerpt = wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );

		$this->post->excerpt = $this->post->post_excerpt = apply_filters('get_the_excerpt', $excerpt, $this->post);

		remove_filter('excerpt_length', [$this, '_excerptLength'], 20);
		remove_filter('excerpt_more', [$this, '_excerptReadMore'], 20);

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
		$date = apply_filters('iamntz/wp/post-date', [
			'iso' => get_the_date('c', $this->post),
			'display' => get_the_date($format, $this->post),
		], $this->post);

		$date = apply_filters("iamntz/wp/post-date/post-type={$this->post->post_type}", $date, $this->post);

		$this->post->date = apply_filters("iamntz/wp/post-date/post-type=all", $date, $this->post);

		return $this;
	}

	public function withThumbnail($sizes = 'thumbnail', $className = '')
	{
		$thumbID = get_post_thumbnail_id($this->post);

		if (gettype($sizes) !== 'array') {
			$sizes = [$sizes];
		}
		foreach ($sizes as $size) {
			$thumbSrc = wp_get_attachment_image_src($thumbID, $size);

			if (empty($thumbSrc[0])) {
				return $this;
			}

			$attrs = [
				'class' => $className,
			];

			$thumb = [
				'raw' => [
					'src' => $thumbSrc[0],
					'w' => $thumbSrc[1],
					'h' => $thumbSrc[2],
				],
				'html' => wp_get_attachment_image($thumbID, $size, false, $attrs),
			];

			$thumb = apply_filters('iamntz/wp/post-thumbnail', $thumb, $this->post, $size);
			$thumb = apply_filters("iamntz/wp/post-thumbnail/post-type={$this->post->post_type}", $thumb, $this->post, $size);

			$this->post->thumbnail = apply_filters("iamntz/wp/post-thumbnail/post-type=all", $thumb, $this->post, $size);
			$this->post->thumbnails[$size] = apply_filters("iamntz/wp/post-thumbnails/post-type=all", $thumb, $this->post, $size);
		}

		return $this;
	}

	public function withPostClass($postClass = '')
	{
		$postClass = apply_filters('iamntz/wp/post-class', $postClass, $this->post);
		$postClass = apply_filters("iamntz/wp/post-class/post-type={$this->post->post_type}", $postClass, $this->post);
		$postClass = apply_filters("iamntz/wp/post-class/post-type=all", $postClass, $this->post);

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

		$cleanAuthor = apply_filters('iamntz/wp/post-author', $cleanAuthor, $this->post);
		$cleanAuthor = apply_filters("iamntz/wp/post-author/post-type={$this->post->post_type}", $cleanAuthor, $this->post);
		$this->post->author = apply_filters("iamntz/wp/post-author/post-type=all", $cleanAuthor, $this->post);

		return $this;
	}

	public function withAuthorMeta($key, $single = true)
	{
		if (empty($this->post->author)) {
			$this->withAuthor();
		}

		if (is_string($key)) {
			$this->_setAuthorMeta($key, 0, $single);
		} elseif (is_array($key)) {
			array_walk($key, [$this, '_setAuthorMeta'], $single);
		}

		return $this;
	}

	public function _setAuthorMeta($key, $index, $single = true)
	{
		$meta = empty($this->post->author['meta']) ? [] : $this->post->author['meta'];

		if (empty($meta[$key])) {
			$meta[$key] = get_user_meta($this->post->author['ID'], $key, $single);
		}

		$this->post->author['meta'] = $meta;
	}

	public function withMeta($key, $single = true)
	{
		if (is_string($key)) {
			$this->_setPostMeta($key, 0, $single);
		} elseif (is_array($key)) {
			array_walk($key, [$this, '_setPostMeta'], $single);
		}

		return $this;
	}

	public function _setPostMeta($key, $index, $single = true)
	{
		$meta = empty($this->post->meta) ? [] : $this->post->meta;

		if (empty($meta[$key])) {
			$meta[$key] = get_post_meta($this->post->ID, $key, $single);
		}

		$this->post->meta = $meta;
	}

	public function withTerms($taxonomy, $args = [])
	{
		$taxonomy = apply_filters('iamntz/wp/post-terms/taxonomy', $taxonomy, $this->post);
		$taxonomy = apply_filters("iamntz/wp/post-terms/taxonomy/post-type={$this->post->post_type}", $taxonomy, $this->post);
		$taxonomy = apply_filters("iamntz/wp/post-terms/taxonomy/post-type=all", $taxonomy, $this->post);

		$postTerms = wp_get_post_terms($this->postID, $taxonomy, $args);
		$postTerms = array_map(function ($term) {
			$term->permalink = get_term_link($term);
			return $term;
		}, $postTerms);

		$terms = empty($this->post->terms) ? [] : $this->post->terms;
		$terms[$taxonomy] = $postTerms;

		$terms = apply_filters('iamntz/wp/post-terms', $terms, $taxonomy, $this->post);
		$terms = apply_filters("iamntz/wp/post-terms/post-type={$this->post->post_type}", $terms, $taxonomy, $this->post);
		$this->post->terms = apply_filters("iamntz/wp/post-terms/post-type=all", $terms, $taxonomy, $this->post);

		return $this;
	}

	public function withEditLink()
	{
		$this->post->editLink = get_edit_post_link($this->post->ID);
		return $this;
	}

	public function withEditButton($anchor = 'Edit', $className = 'edit-entry-button')
	{
		$this->withEditLink();

		if (!empty($this->post->editLink)) {
			$this->post->editButton = sprintf('<a href="%s" class="%s" target="_blank">%s</a>', $this->post->editLink, $className, $anchor);
		}

		return $this;
	}
}
