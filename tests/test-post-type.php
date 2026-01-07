<?php
/**
 * Class PostTypeTest
 *
 * @package Dennis_Plugin
 */

/**
 * Test case for the dennis_project custom post type.
 */
class PostTypeTest extends WP_UnitTestCase {

	/**
	 * Set up the test environment.
	 */
	public function set_up() {
		parent::set_up();
		dennis_plugin_register_post_type();
	}

	/**
	 * Test that the custom post type is registered.
	 */
	public function test_post_type_is_registered() {
		$this->assertTrue( post_type_exists( 'dennis_project' ) );
	}

	/**
	 * Test that the post type has the correct labels.
	 */
	public function test_post_type_labels() {
		$post_type = get_post_type_object( 'dennis_project' );

		$this->assertEquals( 'Projects', $post_type->labels->name );
		$this->assertEquals( 'Project', $post_type->labels->singular_name );
		$this->assertEquals( 'Add New Project', $post_type->labels->add_new_item );
	}

	/**
	 * Test that the post type is public.
	 */
	public function test_post_type_is_public() {
		$post_type = get_post_type_object( 'dennis_project' );

		$this->assertTrue( $post_type->public );
	}

	/**
	 * Test that the post type has archive enabled.
	 */
	public function test_post_type_has_archive() {
		$post_type = get_post_type_object( 'dennis_project' );

		$this->assertTrue( $post_type->has_archive );
	}

	/**
	 * Test that the post type supports REST API.
	 */
	public function test_post_type_shows_in_rest() {
		$post_type = get_post_type_object( 'dennis_project' );

		$this->assertTrue( $post_type->show_in_rest );
	}

	/**
	 * Test that the post type supports expected features.
	 */
	public function test_post_type_supports() {
		$this->assertTrue( post_type_supports( 'dennis_project', 'title' ) );
		$this->assertTrue( post_type_supports( 'dennis_project', 'editor' ) );
		$this->assertTrue( post_type_supports( 'dennis_project', 'thumbnail' ) );
		$this->assertTrue( post_type_supports( 'dennis_project', 'excerpt' ) );
	}

	/**
	 * Test creating a project post.
	 */
	public function test_can_create_project_post() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'  => 'dennis_project',
				'post_title' => 'Test Project',
			)
		);

		$this->assertGreaterThan( 0, $post_id );
		$this->assertEquals( 'dennis_project', get_post_type( $post_id ) );
		$this->assertEquals( 'Test Project', get_the_title( $post_id ) );
	}

	/**
	 * Test the rewrite slug.
	 */
	public function test_post_type_rewrite_slug() {
		$post_type = get_post_type_object( 'dennis_project' );

		$this->assertEquals( 'projects', $post_type->rewrite['slug'] );
	}
}
