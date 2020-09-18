/**
 * Scroller is a beautifully designed scroll bar for any element on a page or a whole WordPress page.
 * Exclusively on Envato Market: https://1.envato.market/scroller
 *
 * @encoding        UTF-8
 * @version         1.1.5
 * @copyright       Copyright (C) 2018 - 2020 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmitry Merkulov (dmitry@merkulov.design), Alexander Khmelnitskiy (info@alexander.khmelnitskiy.ua)
 * @support         help@merkulov.design
 **/

jQuery(function($) {
    
    "use strict";

    jQuery( document ).ready( function () {

        /** Enable disable gradient field */

        var secondaryColorInput = $( "input[name='mdp_scroller_settings[use-gradient-color]']" );
        var secondaryColorRow = $( "input[name='mdp_scroller_settings[gradient-colors-scrollbar]']" ).parent().parent().parent();

        // Initial
        if ( ! secondaryColorInput.is( ':checked' ) ) {
            secondaryColorRow.hide();
        }

        // On status changed
        secondaryColorInput.on( 'click', function() {

            if ( secondaryColorRow.is( 'tr' ) ) {
                if ( $(this).is(':checked') ) {
                    secondaryColorRow.fadeIn();
                } else {
                    secondaryColorRow.fadeOut();
                }
            }

        } );

        /** Min & Max Sliders. */

        if ( document.querySelector( '#mdp-min-length' ) !== null && document.querySelector( '#mdp-max-length' ) !== null ) {

            var sliderInputMin = document.querySelector( '#mdp-min-length' );
            var sliderInputMax = document.querySelector( '#mdp-max-length' );
            var step = 10;

            // Use to get slider value
            sliderInputMin.addEventListener( 'change', function() {

                var Min = sliderInputMin.value;
                var Max = sliderInputMax.value;
                var Res;
                if( parseInt(Min) >=  parseInt(Max) ) {
                    Res = parseInt(Max) + step;
                    sliderInputMax.setSliderValue( Res );
                }

            } );

            sliderInputMax.addEventListener( 'change', function() {

                var Min = sliderInputMin.value;
                var Max = sliderInputMax.value;
                var Res;
                if( parseInt(Min) >=  parseInt(Max) && parseInt(Min) >= step ) {
                    Res = parseInt(Min) - step;
                    sliderInputMin.setSliderValue( Res );
                }

            } );

        }

        /** Initialize CSS Code Editor. */
        var css_editor;
        if ( jQuery( '#mdp_custom_css_fld' ).length ) {

            var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
            editorSettings.codemirror = _.extend(
                {},
                editorSettings.codemirror,
                {
                    indentUnit: 2,
                    tabSize: 2,
                    mode: 'css'
                }
            );

            css_editor = wp.codeEditor.initialize( 'mdp_custom_css_fld', editorSettings );

            css_editor.codemirror.on( 'change', function( cMirror ) {
                css_editor.codemirror.save(); // Save data from CodeEditor to textarea.
                jQuery( '#mdp_custom_css_fld' ).change();
            } );

        }

    } );
    
} );
