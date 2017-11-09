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

	public function __construct($postID = null)
	{
		$this->postID = $postID;
		$this->post = get_post($this->postID, 'OBJECT', 'display');

		$this->post->the_content = apply_filters('the_content', $this->post->post_content);
		$this->post->the_content = str_replace(']]>', ']]&gt;', $this->post->the_content);
	}

	public function get()
	{
		return $this->post;
	}

	public function withDate($format = null)
	{
		$this->post->date = [
			'iso' => get_the_date('c', $this->post),
			'display' => get_the_date($format, $this->post),
		];

		return $this;
	}

	public function withPostClass()
	{
		$this->post->post_class = implode(' ', get_post_class('', $this->post));

		return $this;
	}

	public function withAuthor()
	{

		return $this;
	}

	public function withTerms()
	{
		return $this;
	}
}
