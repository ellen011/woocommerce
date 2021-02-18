<?php
/**
 * Admin Dashboard - Finish Setup
 *
 * @package     WooCommerce\Admin
 * @version     2.1.0
 */

use Automattic\Jetpack\Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Admin_Dashboard_Finish_Setup', false ) ) :

	/**
	 * WC_Admin_Dashboard_Setup Class.
	 */
	class WC_Admin_Dashboard_Finish_Setup {

		/**
		 * List of tasks.
		 *
		 * @var array
		 */
		private $tasks = array(
			'store_details' => array(
				'completed'   => false,
				'button_link' => 'admin.php?page=wc-admin&path=%2Fsetup-wizard',
			),
			'products'      => array(
				'completed'   => false,
				'button_link' => 'admin.php?page=wc-admin&task=products',
			),
			'tax'           => array(
				'completed'   => false,
				'button_link' => 'admin.php?page=wc-admin&task=tax',
			),
			'shipping'      => array(
				'completed'   => false,
				'button_link' => 'admin.php?page=wc-admin&task=shipping',
			),
			'appearance'    => array(
				'completed'   => false,
				'button_link' => 'admin.php?page=wc-admin&task=appearance',
			),
		);

		/**
		 * # of completed tasks.
		 *
		 * @var int
		 */
		private $completed_tasks_count = 0;

		/**
		 * WC_Admin_Dashboard_Finish_Setup constructor.
		 */
		public function __construct() {
			$this->populate_tasks();
			$this->set_completed_tasks();
			$this->should_display_widget() && $this->init();
		}

		/**
		 * Hook meta_box
		 */
		public function init() {
			add_meta_box(
				'wc_admin_dasbharod_finish_setup',
				__( 'WooCommerce Setup', 'woocommerce' ),
				array( $this, 'render' ),
				'dashboard',
				'normal',
				'high'
			);
		}

		/**
		 * Render meta box output.
		 */
		public function render() {
			$version = Constants::get_constant( 'WC_VERSION' );
			wp_enqueue_style( 'wc-dashboard-finish-setup', WC()->plugin_url() . '/assets/css/dashboard-finish-setup.css', array(), $version );

			$task = $this->get_next_task();
			if ( ! $task ) {
				return;
			}

			$button_link           = $task['button_link'];
			$completed_tasks_count = $this->completed_tasks_count;
			$tasks_count           = count( $this->tasks );

			// Given 'r' (circle element's r attr), dashoffset = ((100-$desired_percentage)/100) * PI * (r*2).
			$progress_percentage = ( $completed_tasks_count / $tasks_count ) * 100;
			$circle_r            = 6.5;
			$circle_dashoffset   = ( ( 100 - $progress_percentage ) / 100 ) * ( pi() * ( $circle_r * 2 ) );

			include __DIR__ . '/views/html-admin-dashboard-finish-setup.php';
		}

		/**
		 * Populate tasks from the database.
		 */
		private function populate_tasks() {
			$tasks = get_option( 'woocommerce_task_list_tracked_completed_tasks', array() );
			foreach ( $tasks as $task ) {
				if ( isset( $this->tasks[ $task ] ) ) {
					$this->tasks[ $task ]['completed']   = true;
					$this->tasks[ $task ]['button_link'] = wc_admin_url( $this->tasks[ $task ]['button_link'] );
				}
			}
		}

		/**
		 * Getter for $tasks
		 *
		 * @return array
		 */
		public function get_tasks() {
			return $this->tasks;
		}

		/**
		 * Set # of completed tasks
		 */
		private function set_completed_tasks() {
			$completed_tasks = array_filter(
				$this->tasks,
				function( $task ) {
					return $task['completed'];
				}
			);

			$this->completed_tasks_count = count( $completed_tasks );
		}

		/**
		 * Get the next task.
		 *
		 * @return array|null
		 */
		private function get_next_task() {
			foreach ( $this->get_tasks() as $task ) {
				if ( false === $task['completed'] ) {
					return $task;
				}
			}

			return null;
		}

		/**
		 * Check to see if we should display the widget
		 *
		 * @return bool
		 */
		private function should_display_widget() {
			$all_completed = count( $this->tasks ) === $this->completed_tasks_count;
			return false === $all_completed && 'yes' !== get_option( 'woocommerce_task_list_hidden' );
		}
	}

endif;

return new WC_Admin_Dashboard_Finish_Setup();
