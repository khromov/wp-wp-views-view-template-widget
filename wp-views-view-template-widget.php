<?php
/*
Plugin Name: View Template Widget for Toolset Types & Views
Plugin URI: http://wordpress.org/extend/plugins/wp-views-view-template-widget
Description: Allows you to add a Widget that displays a View Template.
Author: Stanislav Khromov
Version: 1.0
Author URI: http://khromov.wordpress.com
License: GPL2
*/
 
class View_Template_Widget extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array('classname' => 'view_template_widget', 'description' => __( "Displays a View Template Widget on some or all content types.") );
		parent::__construct('view_template_widget', __('WP Views Template'), $widget_ops);
	}

	function widget($args, $instance)
	{
		extract($args);
		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base);
		$view_template = $instance['view_template'];
		$conditionals_enabled = ($instance['conditionals_enabled'] === 'true'); //Casting
		$conditionals_post_list = $instance['conditionals_post_list'];
		
		/**
		 * Calculate whether the widget should be displayed or not.
		 **/ 
		if($conditionals_enabled)
		{
			//Conditional logic performed here
			//if(is_singular()) can be an alternative, but !is_archive() seems better
			if(!is_archive() && !is_front_page() && in_array(get_post_type(get_the_ID()), $conditionals_post_list))
				$show_widget = true;
			else
				$show_widget = false;	
		}
		else
			$show_widget = true;
		
		if($show_widget)
		{
			echo $before_widget;
			
			if ($title)
				echo $before_title . do_shortcode($title) . $after_title;
	
			/** Find View Template and add it **/
			$args = array('p' => (int)$view_template, 'post_type' => 'view-template', 'limit' => 1);
			$current_view = new WP_Query($args);
			
			if(sizeof($current_view->posts)!=0)
			{
				if(strstr($current_view->posts[0]->post_title, "'")!==false)
					echo '<p style="color: red;">'. __('Views Template Widget Error - View Templates with names containing single quotation marks (\') are not supported. Please remove any single quotation marks from the View Template name and try again.') . '</p>';
				else
				{
					$current_view_title = $current_view->posts[0]->post_title;
					//Performs the actual output
					echo do_shortcode("[wpv-post-body view_template='{$current_view_title}']");
				}
			}
			else
			{
				echo '<p style="color: red;">' . __('Views Template Widget Error - could not find View Template with ID: ') . (int)$view_template .'</p>';
			}
			
			echo $after_widget;
		}
	}

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, array('title' => '', 'view_template' => '', 'conditionals_enabled' => 'true', 'conditionals_post_list' => array('post')));
		
		$title = $instance['title'];
		$view_template = $instance['view_template'];
		$conditionals_enabled = $instance['conditionals_enabled'];
		$conditionals_post_list = $instance['conditionals_post_list'];
		
		$args = array('post_type' => 'view-template', 'order' => 'ASC');
		$views_list = new WP_Query($args);
		
		//Get all post types, reference: http://codex.wordpress.org/Function_Reference/get_post_types
		$types = get_post_types(array('public' => true));
		?>
		
		<!-- Widget Title -->
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<strong>
					<?php _e('Title:'); ?>
				</strong>
				<br/>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</label>
			<br/>
			<em style="display: block; margin-top: 4px;">
				<?php _e('Shortcodes can be used in title.'); ?>			
				<br/>
				<?php _e('Leave empty to hide. '); ?>
			</em>
		</p>
		
		<!-- View templates list -->
		<p>
			<strong>
				<?php _e('View Template:'); ?>
			</strong>
			<br/>
			<?php if($views_list->have_posts()) : ?>
				<select id="<?php echo $this->get_field_id('view_template'); ?>" name="<?php echo $this->get_field_name('view_template'); ?>">
					<?php foreach($views_list->posts as $post) : ?>
						<option value="<?php echo $post->ID; ?>"<?php echo $post->ID == $view_template ? ' selected="selected"' : ''?>>
							<?php echo $post->post_title; ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php else: ?>
				<strong>
					<?php _e('No View Templates found.'); ?>
				</strong>
			<?php endif; ?>
		</p>
		
		<!-- Conditional logic on/off -->
		<p>
			<strong>
				<?php _e('Conditional logic preferences:'); ?>
			</strong>		
			<br/>
			<input type="radio" name="<?php echo $this->get_field_name('conditionals_enabled'); ?>" id="<?php echo $this->get_field_id('conditionals_enabled'); ?>_option_1" value="false" <?php echo $conditionals_enabled === "false" ? ' checked="checked"' : '' ?> /> Show widget everywhere <br/>
			<input type="radio" name="<?php echo $this->get_field_name('conditionals_enabled'); ?>" id="<?php echo $this->get_field_id('conditionals_enabled'); ?>_option_2" value="true"  <?php echo $conditionals_enabled === "true" ? ' checked="checked"' : '' ?>/> Show widget when viewing the following post types: <br/>
		</p>
		
		<!-- Checkbox list of Post types -->
		<p>
			<strong>
				<?php _e('Show widget on the following post types:'); ?>
			</strong>		
			<br/>
			<?php foreach($types as $type_key => $type) : ?>
				<input type="checkbox" name="<?php echo $this->get_field_name('conditionals_post_list'); ?>[]" id="<?php echo $this->get_field_id('conditionals_post_list'); ?>_<?php echo $type; ?>" value="<?php echo $type; ?>" <?php echo in_array($type, $conditionals_post_list) ? ' checked="checked"' : '' ?>> <?php echo $type ?> <br/>
			<?php endforeach; ?>
		</p>
		
		<?php
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array('title' => '', 'view_template' => '', 'conditionals_enabled' => 'true', 'conditionals_post_list' => array('post')));
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['view_template'] = (int)($new_instance['view_template']);
		$instance['conditionals_enabled'] = $new_instance['conditionals_enabled'];
		$instance['conditionals_post_list'] = $new_instance['conditionals_post_list'];
		
		return $instance;
	}
}

add_action('widgets_init', create_function('', 'return register_widget("View_Template_Widget");'));?>