<?php
/**
 * Database Integration Tests
 *
 * Tests that demonstrate WordPress database operations using the test database.
 *
 * @package EmploymentHero\Tests\Integration
 */

declare(strict_types=1);

namespace EmploymentHero\Tests\Integration;

use WP_UnitTestCase;

/**
 * Test class for WordPress database operations.
 */
class DatabaseTest extends WP_UnitTestCase {
    /**
     * Test creating and retrieving a post from database.
     *
     * @return void
     */
    public function test_create_and_retrieve_post(): void {
        // Create a post.
        $post_id = wp_insert_post(
            [
                'post_title'   => 'Test Database Post',
                'post_content' => 'This is test content stored in wordpress_test database.',
                'post_status'  => 'publish',
                'post_type'    => 'post',
            ]
        );

        $this->assertIsInt( $post_id );
        $this->assertGreaterThan( 0, $post_id );

        // Retrieve the post from database.
        $post = get_post( $post_id );

        $this->assertInstanceOf( 'WP_Post', $post );
        $this->assertSame( 'Test Database Post', $post->post_title );
        $this->assertSame( 'publish', $post->post_status );
    }

    /**
     * Test post meta operations.
     *
     * @return void
     */
    public function test_post_meta_operations(): void {
        // Create a post.
        $post_id = self::factory()->post->create();

        // Add meta data.
        add_post_meta( $post_id, 'eh_custom_field', 'custom_value' );
        add_post_meta( $post_id, 'eh_numeric_field', 42 );

        // Retrieve and verify meta data.
        $custom_value  = get_post_meta( $post_id, 'eh_custom_field', true );
        $numeric_value = get_post_meta( $post_id, 'eh_numeric_field', true );

        $this->assertSame( 'custom_value', $custom_value );
        $this->assertEquals( 42, $numeric_value );

        // Update meta data.
        update_post_meta( $post_id, 'eh_custom_field', 'updated_value' );
        $updated_value = get_post_meta( $post_id, 'eh_custom_field', true );

        $this->assertSame( 'updated_value', $updated_value );

        // Delete meta data.
        delete_post_meta( $post_id, 'eh_custom_field' );
        $deleted_value = get_post_meta( $post_id, 'eh_custom_field', true );

        $this->assertEmpty( $deleted_value );
    }

    /**
     * Test user creation and capabilities.
     *
     * @return void
     */
    public function test_user_creation_and_roles(): void {
        // Create users with different roles.
        $admin_id      = self::factory()->user->create( [ 'role' => 'administrator' ] );
        $editor_id     = self::factory()->user->create( [ 'role' => 'editor' ] );
        $subscriber_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );

        // Verify users exist in database.
        $admin      = get_userdata( $admin_id );
        $editor     = get_userdata( $editor_id );
        $subscriber = get_userdata( $subscriber_id );

        $this->assertInstanceOf( 'WP_User', $admin );
        $this->assertInstanceOf( 'WP_User', $editor );
        $this->assertInstanceOf( 'WP_User', $subscriber );

        // Verify capabilities.
        $this->assertTrue( user_can( $admin_id, 'manage_options' ) );
        $this->assertFalse( user_can( $editor_id, 'manage_options' ) );
        $this->assertTrue( user_can( $editor_id, 'edit_posts' ) );
        $this->assertFalse( user_can( $subscriber_id, 'edit_posts' ) );
    }

    /**
     * Test custom database query using wpdb.
     *
     * @return void
     */
    public function test_direct_database_query(): void {
        global $wpdb;

        // Create some posts.
        $post_ids = self::factory()->post->create_many( 5, [ 'post_status' => 'publish' ] );

        // Direct database query.
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = %s AND post_type = %s",
                'publish',
                'post'
            )
        );

        $this->assertGreaterThanOrEqual( 5, (int) $count );

        // Query specific posts.
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM {$wpdb->posts} WHERE ID IN (" . implode( ',', array_fill( 0, count( $post_ids ), '%d' ) ) . ')',
                ...$post_ids
            )
        );

        $this->assertCount( 5, $results );
    }

    /**
     * Test taxonomy and term operations.
     *
     * @return void
     */
    public function test_taxonomy_operations(): void {
        // Create a post.
        $post_id = self::factory()->post->create();

        // Create and assign categories.
        $category_id = wp_create_category( 'Test Category' );
        wp_set_post_categories( $post_id, [ $category_id ] );

        // Verify category assignment.
        $categories = wp_get_post_categories( $post_id );
        $this->assertContains( $category_id, $categories );

        // Create and assign tags.
        wp_set_post_tags( $post_id, [ 'tag1', 'tag2', 'tag3' ] );
        $tags = wp_get_post_tags( $post_id );

        $this->assertCount( 3, $tags );

        $tag_names = wp_list_pluck( $tags, 'name' );
        $this->assertContains( 'tag1', $tag_names );
        $this->assertContains( 'tag2', $tag_names );
        $this->assertContains( 'tag3', $tag_names );
    }

    /**
     * Test options API with database.
     *
     * @return void
     */
    public function test_options_api(): void {
        $option_name = 'eh_test_option_' . time();

        // Add option.
        $added = add_option( $option_name, 'test_value' );
        $this->assertTrue( $added );

        // Get option.
        $value = get_option( $option_name );
        $this->assertSame( 'test_value', $value );

        // Update option.
        update_option( $option_name, 'updated_value' );
        $updated_value = get_option( $option_name );
        $this->assertSame( 'updated_value', $updated_value );

        // Delete option.
        delete_option( $option_name );
        $deleted_value = get_option( $option_name, 'default' );
        $this->assertSame( 'default', $deleted_value );
    }

    /**
     * Test transient API with database storage.
     *
     * @return void
     */
    public function test_transient_api(): void {
        $transient_name = 'eh_test_transient_' . time();

        // Set transient.
        set_transient( $transient_name, 'transient_value', 3600 );

        // Get transient.
        $value = get_transient( $transient_name );
        $this->assertSame( 'transient_value', $value );

        // Delete transient.
        delete_transient( $transient_name );
        $deleted_value = get_transient( $transient_name );
        $this->assertFalse( $deleted_value );
    }

    /**
     * Test comment operations.
     *
     * @return void
     */
    public function test_comment_operations(): void {
        // Create a post.
        $post_id = self::factory()->post->create();

        // Create comments.
        $comment_id = wp_insert_comment(
            [
                'comment_post_ID'  => $post_id,
                'comment_author'   => 'Test Author',
                'comment_content'  => 'This is a test comment.',
                'comment_approved' => 1,
            ]
        );

        $this->assertIsInt( $comment_id );
        $this->assertGreaterThan( 0, $comment_id );

        // Retrieve comment.
        $comment = get_comment( $comment_id );
        $this->assertInstanceOf( 'WP_Comment', $comment );
        $this->assertSame( 'Test Author', $comment->comment_author );

        // Get comments for post.
        $comments = get_comments( [ 'post_id' => $post_id ] );
        $this->assertCount( 1, $comments );
    }
}
