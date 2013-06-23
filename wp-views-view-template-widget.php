<?php
/*
Plugin Name: View Template Widget for Toolset Types & Views
Plugin URI: http://wordpress.org/extend/plugins/wp-views-view-template-widget
Description: Allows you to add a View Template Widget for a particular post type.
Author: Stanislav Khromov
Version: 1.0
Author URI: http://khromov.wordpress.com
License: GPL2
*/
 
class View_Template_Widget extends WP_Widget
{

	function __construct()
	{
		$widget_ops = array('classname' => 'view_template_widget', 'description' => __( "Add a View Template Widget for a particular post type") );
		parent::__construct('view_template_widget', __('View Template Widget'), $widget_ops);
	}

	function widget($args, $instance)
	{
		extract($args);
		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base);
		$view_template = $instance['view_template'];

		echo $before_widget;
		if ($title)
			echo $before_title . do_shortcode($title) . $after_title;

		/** Find View Template and add it **/
		$args = array('p' => (int)$view_template, 'post_type' => 'view-template', 'limit' => 1);
		$current_view = new WP_Query($args);
		
		if(sizeof($current_view->posts)!=0)
		{
			$current_view_title = $current_view->posts[0]->post_title;
			echo do_shortcode('[wpv-post-body view_template="'. $current_view_title .'"]');
		}
		else
		{
			echo '<p style="color: red;">Views Template Widget Error - could not find View Template with ID '. (int)$view_template .'</p>';
		}
		
		echo $after_widget;	
	}

	function form( $instance )
	{
		$instance = wp_parse_args((array)$instance, array('title' => '', 'view_template' => '') );
		$title = $instance['title'];
		$view_template = $instance['view_template'];
		
		$args = array('post_type' => 'view-template', 'order' => 'ASC');
		$views_list = new WP_Query($args);   
 
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<strong>
					<?php _e('Title:'); ?>
				</strong>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</label>
			<br/>
			<em style="display: block; margin-top: 4px;">
				<?php _e('Leave empty to hide the title.'); ?>			
			</em>
		</p>
		<?php if($views_list->have_posts()) : ?>
			<select id="<?php echo $this->get_field_id('view_template'); ?>" name="<?php echo $this->get_field_name('view_template'); ?>">
				<?php foreach($views_list->posts as $post) : ?>
					<option value="<?php echo $post->ID; ?>"<?php echo $post->ID == $view_template ? ' selected="selected"' : ''?>>
						<?php echo $post->post_title; ?> - <?php echo $post->ID; ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php else: ?>
			<strong>
				No View Templates found.
			</strong>
		<?php endif; ?>
<?php
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => '', 'view_template' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['view_template'] = strip_tags($new_instance['view_template']);
		return $instance;
	}
}

add_action('widgets_init', create_function('', 'return register_widget("View_Template_Widget");'));?>