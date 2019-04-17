<?php
/**
 * WP_Framework_Admin Traits Dashboard
 *
 * @version 0.0.24
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space/
 */

namespace WP_Framework_Admin\Traits;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Trait Dashboard
 * @package WP_Framework_Admin\Traits
 * @property \WP_Framework $app
 * @mixin \WP_Framework_Core\Traits\Singleton
 * @mixin \WP_Framework_Presenter\Traits\Presenter
 */
trait Dashboard {

	/**
	 * @return int
	 */
	public function get_load_priority() {
		return 0;
	}

	/**
	 * @return string
	 */
	public function get_page_title() {
		return 'Dashboard';
	}

	/**
	 * @return array
	 */
	protected abstract function get_setting_list();

	/**
	 * @return \Generator
	 */
	private function _get_setting_list() {
		foreach ( $this->get_setting_list() as $name => $option ) {
			if ( is_int( $name ) && is_string( $option ) ) {
				$name   = $option;
				$option = [];
			}
			yield $name => $option;
		}
	}

	/**
	 * post
	 */
	protected function post_action() {
		if ( $this->app->input->post( 'update' ) ) {
			$this->pre_update();
			foreach ( $this->_get_setting_list() as $name => $option ) {
				$this->update_setting( $name, $option );
			}
			$this->app->add_message( 'Settings have been updated.', 'setting' );
		} else {
			$this->pre_delete();
			foreach ( $this->_get_setting_list() as $name => $option ) {
				$this->app->option->delete( $this->get_filter_prefix() . $name );
				$this->delete_hook_cache( $name );
			}
			$this->app->add_message( 'Settings have been reset.', 'setting' );
		}
	}

	/**
	 * pre update
	 */
	protected function pre_update() {

	}

	/**
	 * pre delete
	 */
	protected function pre_delete() {

	}

	/**
	 * @return array
	 */
	protected function get_view_args() {
		$args = [];
		foreach ( $this->_get_setting_list() as $name => $option ) {
			$args['settings'][ $name ] = $this->get_view_setting( $name, $option );
		}

		return $this->filter_view_args( $args );
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	protected function filter_view_args( array $args ) {
		return $args;
	}

	/**
	 * @param string $name
	 * @param array $option
	 *
	 * @return array
	 */
	protected function get_view_setting( $name, $option ) {
		$detail          = $this->app->setting->get_setting( $name, true );
		$detail['id']    = str_replace( '/', '-', $detail['name'] );
		$detail['form']  = $this->app->array->get( $option, 'form', function () use ( $detail ) {
			return $this->get_form_by_type( $this->app->array->get( $detail, 'type', '' ), false );
		} );
		$detail['title'] = $this->translate( $detail['label'] );
		$detail['label'] = $detail['title'];

		$detail = $this->filter_detail( $detail, $name, $option );
		$detail = $this->get_type_setting( $name, $this->app->array->get( $detail, 'type' ), $detail, $option );
		$detail = $this->get_form_setting( $name, $this->app->array->get( $detail, 'form' ), $detail, $option );

		return $this->filter_view_setting( $detail, $name, $option );
	}

	/**
	 * @param array $detail
	 * @param string $name
	 * @param array $option
	 *
	 * @return array
	 */
	protected function filter_detail(
		/** @noinspection PhpUnusedParameterInspection */
		$detail, $name, array $option
	) {
		return $detail;
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param array $detail
	 * @param array $option
	 *
	 * @return array
	 */
	protected function get_type_setting( $name, $type, array $detail, array $option ) {
		if ( $type === 'bool' ) {
			if ( $detail['value'] ) {
				$detail['checked'] = true;
			}
			$detail['value'] = 1;
			$detail['label'] = $this->translate( $this->get_checkbox_label( $name, $option ) );

			return $this->filter_type_setting( $name, $type, $detail, $option );
		}

		if ( 'float' === $type ) {
			$detail['attributes']['step'] = $this->app->array->get( $option, 'step', '0.01' );
		}
		if ( 'int' === $type || 'float' === $type ) {
			$min = $this->app->array->get( $option, 'min', function () use ( $detail ) {
				return $this->app->array->get( $detail, 'min' );
			} );
			$max = $this->app->array->get( $option, 'max', function () use ( $detail ) {
				return $this->app->array->get( $detail, 'max' );
			} );
			isset( $min ) and $detail['attributes']['min'] = $min;
			isset( $max ) and $detail['attributes']['max'] = $max;
		}

		return $this->filter_type_setting( $name, $type, $detail, $option );
	}

	/**
	 * @param string $name
	 * @param array $option
	 *
	 * @return string
	 */
	protected function get_checkbox_label(
		/** @noinspection PhpUnusedParameterInspection */
		$name, array $option
	) {
		return $this->app->array->get( $option, 'checkbox_label', 'Yes' );
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param array $detail
	 * @param array $option
	 *
	 * @return array
	 */
	protected function filter_type_setting(
		/** @noinspection PhpUnusedParameterInspection */
		$name, $type, array $detail, array $option
	) {
		return $detail;
	}

	/**
	 * @param string $name
	 * @param string $form
	 * @param array $detail
	 * @param array $option
	 *
	 * @return array
	 */
	protected function get_form_setting( $name, $form, array $detail, array $option ) {
		if ( 'select' === $form ) {
			$value              = $detail['value'];
			$options            = $this->app->array->get( $option, 'options', [] );
			$detail['selected'] = $value;
			if ( ! isset( $options[ $value ] ) ) {
				$options[ $value ] = $value;
			}
			$detail['options'] = $options;
		} elseif ( 'multi_select' === $form ) {
			$value              = $detail['value'];
			$options            = $this->app->array->get( $option, 'options', [] );
			$detail['form']     = 'select';
			$detail['multiple'] = true;
			$detail['selected'] = $this->app->string->explode( $value, $this->get_delimiter( $option ) );
			$detail['options']  = $options;
			$detail['size']     = count( $options );
			$detail['name']     .= '[]';
		}

		return $this->filter_form_setting( $name, $form, $detail, $option );
	}

	/**
	 * @param string $name
	 * @param string $form
	 * @param array $detail
	 * @param array $option
	 *
	 * @return array
	 */
	protected function filter_form_setting(
		/** @noinspection PhpUnusedParameterInspection */
		$name, $form, array $detail, array $option
	) {
		return $detail;
	}

	/**
	 * @param array $detail
	 * @param string $name
	 * @param array $option
	 *
	 * @return array
	 */
	protected function filter_view_setting(
		/** @noinspection PhpUnusedParameterInspection */
		array $detail, $name, array $option
	) {
		return $detail;
	}

	/**
	 * @param string $name
	 * @param array $option
	 *
	 * @return bool
	 */
	protected function update_setting( $name, $option ) {
		$detail  = $this->app->setting->get_setting( $name, true );
		$default = null;
		if ( $this->app->array->get( $detail, 'type' ) === 'bool' ) {
			$default = 0;
		}
		if ( $this->app->array->get( $option, 'form' ) === 'multi_select' ) {
			$this->app->input->set_post( $detail['name'], $this->app->string->implode( $this->app->input->post( $detail['name'] ), $this->get_delimiter( $option ) ) );
		}

		return $this->app->option->set_post_value( $detail['name'], $default );
	}

	/**
	 * @param array $option
	 *
	 * @return string
	 */
	protected function get_delimiter( $option ) {
		return $this->app->array->get( $option, 'delimiter', ',' );
	}
}
