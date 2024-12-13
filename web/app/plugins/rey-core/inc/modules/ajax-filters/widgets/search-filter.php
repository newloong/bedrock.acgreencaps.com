<?php
/**
 * Rey Ajax Product Filter
 */
if (!class_exists('REYAJAXFILTERS_Search_Filter_Widget')) {
	class REYAJAXFILTERS_Search_Filter_Widget extends WP_Widget {
		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			parent::__construct(
				'reyajfilter-search-filter', // Base ID
				__('Filter - Search', 'rey-core'), // Name
				array('description' => __('Filter WooCommerce products by keywords.', 'rey-core')) // Args
			);

			$this->defaults = [
				'title'                   => '',
				'placeholder'             => '',
				// Advanced
				'show_hide_categories'    => 'hide',
				'show_only_on_categories' => [],
				'selective_display' => [],
			];
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget($args, $instance) {

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			if( \ReyCore\Modules\AjaxFilters\Base::should_hide_widget($instance) ){
				return;
			}

			$html = '';

			// required scripts
			// enqueue necessary scripts
			\ReyCore\Modules\AjaxFilters\Base::load_scripts();

			reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav-search');

			// get values from url
			$value = ( isset($_REQUEST['keyword']) && !empty($_REQUEST['keyword'])) ? reycore__clean($_REQUEST['keyword']) : '';

			extract($args);

			$id = $widget_id . '-search';

			$html .= '<div class="reyajfilter-search-filter reyajfilter-searchbox">';
			$html .= reycore__get_svg_icon(['id'=>'search']);
			$html .= sprintf('<input type="search" id="%1$s" name="%1$s" value="%2$s" data-key="keyword" placeholder="%3$s" />', $id, $value, $instance['placeholder'] );
			$html .= '</div>';

			$widget_class = 'woocommerce reyajfilter-search-filter-widget';

			// no class found, so add it
			if (strpos($before_widget, 'class') === false) {
				$before_widget = str_replace('>', 'class="' . $widget_class . '"', $before_widget);
			}
			// class found but not the one that we need, so add it
			else {
				$before_widget = str_replace('class="', 'class="' . $widget_class . ' ', $before_widget);
			}

			echo $before_widget;

			if (!empty($instance['title'])) {
				echo $args['before_title'] . apply_filters('widget_title', $instance['title'], $instance). $args['after_title'];
			}

			echo $html;

			echo $args['after_widget'];
		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form($instance) {

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$widget_fields = new \ReyCore\Modules\AjaxFilters\WidgetFields($this, $instance);

			do_action('reycore/ajaxfilters/before_widget_controls', $instance); ?>

			<div class="rey-widgetTabs-wrapper">

				<div class="rey-widgetTabs-buttons">
					<span data-tab="basic" class="--active"><?php esc_html_e('Basic options', 'rey-core') ?></span>
					<span data-tab="advanced"><?php esc_html_e('Advanced', 'rey-core') ?></span>
				</div>

				<div class="rey-widgetTabs-tabContent --active" data-tab="basic">

					<?php

					$widget_fields->add_field([
						'name' => 'title',
						'type' => 'text',
						'label' => __( 'Title', 'rey-core' ),
						'value' => '',
						'field_class' => 'widefat'
					]);

					$widget_fields->add_field([
						'name' => 'placeholder',
						'type' => 'text',
						'label' => __( 'Placeholder', 'rey-core' ),
						'value' => '',
						'field_class' => 'widefat',
						'placeholder' => esc_html__('eg: Type to search', 'rey-core')
					]);

					?>
				</div>
				<!-- end tab -->

				<div class="rey-widgetTabs-tabContent" data-tab="advanced">

					<?php
					$widget_fields->add_field([
						'name' => 'show_hide_categories',
						'type' => 'select',
						'label' => __( 'Show or Hide widget on certain categories:', 'rey-core' ),
						'value' => 'hide',
						'options' => [
							'show' => esc_html__('Show', 'rey-core'),
							'hide' => esc_html__('Hide', 'rey-core'),
						]
					]);

					$widget_fields->add_field([
						'name' => 'show_only_on_categories',
						'type' => 'select',
						'multiple' => true,
						'label' => __( 'Categories:', 'rey-core' ),
						'wrapper_class' => '--stretch',
						'options' => function_exists('reycore_wc__product_categories') ? reycore_wc__product_categories() : []
					]);

					echo '<hr>';

					$default_selective_display = [];

					if( isset($instance['selective_display']) && 'cat_attr_tag' === $instance['selective_display'] ){
						$default_selective_display = ['cat', 'attr', 'tag'];
					}

					$widget_fields->add_field([
						'name' => 'selective_display',
						'type' => 'select',
						'multiple' => true,
						'label' => __( 'Display widget only on:', 'rey-core' ),
						'value' => $default_selective_display,
						'wrapper_class' => '--stretch',
						'options' => [
							'shop' => esc_html__('Shop Page', 'rey-core'),
							'search' => esc_html__('Search Page', 'rey-core'),
							'cat' => esc_html__('Product Categories', 'rey-core'),
							'attr' => esc_html__('Product Attributes (public archive)', 'rey-core'),
							'tag' => esc_html__('Product Tags (archive)', 'rey-core'),
						]
					]); ?>

				</div>
				<!-- end tab -->

			</div>
			<?php
			reyajaxfilters__filter_admin_titles( $instance['show_only_on_categories'], $instance['show_hide_categories'] );
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update($new_instance, $old_instance) {
			$instance = [];

			foreach ($this->defaults as $key => $value) {
				$instance[$key] = isset($new_instance[$key]) ? reycore__clean( $new_instance[$key] ) : $value;
			}

			return $instance;
		}
	}
}

// register widget
if (!function_exists('reyajaxfilter_register_search_filter_widget')) {
	function reyajaxfilter_register_search_filter_widget() {
		register_widget('REYAJAXFILTERS_Search_Filter_Widget');
	}
	add_action('widgets_init', 'reyajaxfilter_register_search_filter_widget');
}
