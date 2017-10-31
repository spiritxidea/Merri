<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * @todo: implement a way to display fields with custom values, not from options. for example: event edit page
 */

if ( ! class_exists( 'PixelYourSite\Settings_API' ) ) :

    abstract class Settings_API {

        /**
         * Database option key.
         *
         * @var string
         */
        protected $option_key = '';

        /**
         * Default setting values.
         *
         * @var array
         */
        protected $setting_defaults = array();

        /**
         * Setting values.
         *
         * @var array
         */
        protected $settings_values = array();

        /**
         * Form fields.
         *
         * @var array
         */
        protected $form_fields = array();

        /**
         * Settings_API constructor.
         *
         * @param       $scope
         * @param array $defaults
         */
        public function __construct( $scope, $defaults = array() ){

            $this->option_key       = 'pys_' . $scope;
            $this->setting_defaults = $defaults;

        }

        /**
         * Initialise Settings.
         *
         * Store all settings in a single database entry
         * and make sure the $settings array is either the default
         * or the settings stored in the database.
         */
        private function init_settings(){

            if ( empty( $this->settings_values ) ) {
                $this->settings_values = get_option( $this->get_option_key(), null );
            }

            // if there are no settings defined, use default values
            if ( ! is_array( $this->settings_values ) ) {
                $this->settings_values = $this->setting_defaults;
            }

        }

        /**
         * Gets an option from the settings API, using defaults if necessary to prevent undefined notices.
         *
         * @param  string $key
         * @param  mixed  $empty_value
         *
         * @return mixed The value specified for the option or a default value for the option.
         */
        public function get_option( $key, $empty_value = null ){

            $this->init_settings();

            // get option default if unset
            if ( ! isset( $this->settings_values[ $key ] ) ) {
                $this->settings_values[ $key ] = isset( $this->setting_defaults[ $key ] )
                    ? $this->setting_defaults[ $key ] : '';
            }

            // return fall back value if empty and no default has been defined
            if ( ! is_null( $empty_value ) && '' === $this->settings_values[ $key ] ) {
                $this->settings_values[ $key ] = $empty_value;
            }

            return $this->settings_values[ $key ];

        }

        private function get_option_key(){
            return $this->option_key;
        }

        /**
         * Prefix key for settings.
         *
         * @param  mixed $key
         *
         * @return string
         */
        private function get_field_key( $key ){
            return $this->option_key . '_' . $key;
        }

        /**
         * Get sanitized field value from $_POST data or $values param if present.
         *
         * @param string     $key    Option key (name).
         * @param string     $type   Option sanitization type.
         *
         * @param null|array $values Optional. If set, options values will be received from param instead of $_POST.
         *
         * @return mixed Sanitized option value.
         */
        public function get_field_value( $key, $type, $values = null ){

            // get values from $_POST data
            if ( ! is_array( $values ) ) {
                $field_key = $this->get_field_key( $key );
                $value     = isset( $_POST[ $field_key ] ) ? $_POST[ $field_key ] : null;
            } else {
                $value = isset( $values[ $key ] ) ? $values[ $key ] : null;
            }

            // Look for a custom sanitization filter
            $filter_name = "{$this->option_key}_settings_sanitize_{$key}_field";
            if ( has_filter( $filter_name ) ) {
                return apply_filters( $filter_name, $value );
            }

            // Look for a sanitize_FIELDTYPE_field method
            if ( is_callable( array( $this, 'sanitize_' . $type . '_field' ) ) ) {
                return $this->{'sanitize_' . $type . '_field'}( $key, $value );
            }

            // Fallback to text
            return $this->sanitize_text_field( $key, $value );

        }

        /**
         * Sanitize and saves options.
         *
         * @param string     $section Options section to update.
         * @param null|array $values  Optional. If set, options values will be received from param instead of $_POST.
         */
        public function update_options( $section, $values = null ){

            $this->init_settings();

            // select fields from desired section to avoid overwriting all module options
            $form_fields = isset( $this->form_fields[ $section ] ) ? $this->form_fields[ $section ] : array();

            // sanitize each option in section
            foreach ( $form_fields as $key => $field_type ) {
                $this->settings_values[ $key ] = $this->get_field_value( $key, $field_type, $values );
            }

            update_option( $this->get_option_key(), $this->settings_values );

        }

        /**
         * Output checkbox HTML.
         */
        public function render_checkbox_html( $key, $label, $disabled = false ){

            $field_key = $this->get_field_key( $key );

            ?>

            <input <?php disabled( $disabled, true ); ?> type="checkbox" name="<?php echo esc_attr( $field_key ); ?>"
                                                         id="<?php echo esc_attr( $field_key ); ?>" <?php checked( $this->get_option( $key ),
                true ); ?> value="1">
            <label for="<?php echo esc_attr( $field_key ); ?>"
                   class="control-label"><?php echo wp_kses_post( $label ); ?></label>

            <?php

        }
    
        /**
         * Output "switchery" checkbox HTML.
         *
         * @param string $key      Option name.
         * @param string $label    Input label.
         * @param bool   $disabled Optional. Input disabled attribute.
         * @param mixed  $value    Optional. Input value.
         */
        public function render_switchery_html( $key, $label, $disabled = false, $value = 1 ){

            $field_key = $this->get_field_key( $key );

            ?>

            <input  type="checkbox" 
                    name="<?php echo esc_attr( $field_key ); ?>"
                    id="<?php echo esc_attr( $field_key ); ?>"
                    value="<?php echo esc_attr( $value ); ?>"
                    data-plugin="switchery"
                    data-switchery="true"
                    style="display: none;"
                <?php disabled( $disabled, true ); ?>
                <?php checked( $this->get_option( $key ), $value ); ?>    
            >
            <label for="<?php echo esc_attr( $field_key ); ?>"
                   class="control-label"><?php echo wp_kses_post( $label ); ?></label>

            <?php

        }

        /**
         * Output radio HTML.
         */
        public function render_radio_html( $key, $value, $label, $disabled = false ){

            $field_key = $this->get_field_key( $key );
            $field_id  = $field_key . '_' . $value;

            ?>

            <input <?php disabled( $disabled, true ); ?> type="radio" name="<?php esc_attr_e( $field_key ); ?>"
                                                         id="<?php esc_attr_e( $field_id ); ?>" <?php checked( $this->get_option( $key ),
                $value ); ?> value="<?php esc_attr_e( $value ); ?>">
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo wp_kses_post( $label ); ?></label>

            <?php

        }

        /**
         * Output text input HTML.
         */
        public function render_text_html( $key, $placeholder = '', $disabled = false ){

            $field_key   = $this->get_field_key( $key );
            $field_value = $this->get_option( $key );

            ?>

            <input <?php disabled( $disabled ); ?> type="text" name="<?php esc_attr_e( $field_key ); ?>"
                                                   id="<?php esc_attr_e( $field_key ); ?>"
                                                   value="<?php esc_attr_e( $field_value ); ?>"
                                                   placeholder="<?php esc_attr_e( $placeholder ); ?>"
                                                   class="form-control">

            <?php

        }
        
        /**
         * Output URL input HTML.
         */
        public function render_url_html( $key, $placeholder = '', $disabled = false ){
            
            $field_key   = $this->get_field_key( $key );
            $field_value = $this->get_option( $key );
            
            ?>
            
            <input <?php disabled( $disabled ); ?> type="url" name="<?php esc_attr_e( $field_key ); ?>"
                                                   id="<?php esc_attr_e( $field_key ); ?>"
                                                   value="<?php esc_attr_e( $field_value ); ?>"
                                                   placeholder="<?php esc_attr_e( $placeholder ); ?>"
                                                   class="form-control">
            
            <?php
            
        }

        public function render_select_html( $key, $options, $disabled = false ) {
    
            $field_key = $this->get_field_key( $key );

            ?>

            <select class="form-control" name="<?php echo esc_attr( $field_key ); ?>"
                    id="<?php echo esc_attr( $field_key ); ?>" <?php disabled( $disabled ); ?> style="width: 100%;">

                <option value="" disabled selected>Please, select...</option>

                <?php foreach ( $options as $option_key => $option_value ) : ?>
                    <option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key,
                        esc_attr( $this->get_option( $key ) ) ); ?> <?php disabled( $option_key,
                        'disabled' ); ?>><?php echo esc_attr( $option_value ); ?></option>
                <?php endforeach; ?>
            </select>

            <?php
        }

        public function render_multi_select_html( $key, $options, $disabled = false ) {

            $field_key = $this->get_field_key( $key );
            $selected  = $this->get_option( $key );

            ?>

            <select class="form-control pys-select2" name="<?php echo esc_attr( $field_key ); ?>[]"
                    id="<?php echo esc_attr( $field_key ); ?>" <?php disabled( $disabled ); ?> style="width: 100%;"
                    multiple="multiple">

                <?php foreach ( $options as $option_key => $option_value ) : ?>
                    <option value="<?php echo esc_attr( $option_key ); ?>"
                        <?php selected( in_array( $option_key, $selected ) ); ?>
                        <?php disabled( $option_key, 'disabled' ); ?>
                    >
                        <?php echo esc_attr( $option_value ); ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <?php
        }

        /**
         * Output textarea input HTML.
         */
        public function render_textarea_html( $key, $placeholder = '', $disabled = false, $rows = 10 ){

            $field_key   = $this->get_field_key( $key );
            $field_value = $this->get_option( $key );

            ?>

            <textarea <?php disabled( $disabled ); ?> name="<?php esc_attr_e( $field_key ); ?>"
                                                      id="<?php esc_attr_e( $field_key ); ?>"
                                                      rows="<?php esc_attr_e( $rows ); ?>"
                                                      placeholder="<?php esc_attr_e( $placeholder ); ?>"
                                                      class="form-control"><?php esc_html_e( $field_value ); ?></textarea>

            <?php

        }

        /**
         * Sanitize Text Field.
         */
        public function sanitize_text_field( $key, $value ){

            $value = is_null( $value ) ? '' : $value;

            //@todo: maybe remove wp_kses_post
            return wp_kses_post( trim( stripslashes( $value ) ) );

        }

        /**
         * Sanitize checkbox field value.
         *
         * Boolean and numeric values are treated as boolean.
         * String values are treated as string or null.
         *
         * @param string $key   Option name.
         * @param mixed  $value Option value to sanitize.
         *
         * @return bool|null|string
         */
        public function sanitize_checkbox_field( $key, $value ) {

            if ( is_null( $value ) ) {
                return false;
            } else if ( is_bool( $value ) || is_numeric( $value ) ) {
                return (bool) $value;
            } else {
                return is_null( $value ) ? null : sanitize_text_field( $value );
            }
            
        }

        /**
         * Sanitize Radio Field.
         */
        public function sanitize_radio_field( $key, $value ){
            return ! is_null( $value ) ? trim( stripslashes( $value ) ) : null;
        }

        /**
         * Sanitize Select Field.
         *
         * @param  string $key
         * @param  string $value Posted Value
         *                       
         * @see pys_clean()                      
         *
         * @return string
         */
        public function sanitize_select_field( $key, $value ){

            $value = is_null( $value ) ? '' : $value;

            return pys_clean( stripslashes( $value ) );

        }

        /**
         * Sanitize Multi Select Field.
         *
         * @param  string $key
         * @param  array  $value Posted Value
         *
         * @see pys_clean()
         *
         * @return array
         */
        public function sanitize_multi_select_field( $key, $value ) {
            return is_array( $value ) ? array_map( 'PixelYourSite\pys_clean', $value ) : array();
        }

        /**
         * Sanitize Textarea Field.
         */
        public function sanitize_textarea_field( $key, $value ){

            $value = is_null( $value ) ? '' : $value;

            return trim( stripslashes( $value ) );

        }

    }

endif;