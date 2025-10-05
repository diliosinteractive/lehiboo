(function($) {
    "use strict";
    const attachShadow = Element.prototype.attachShadow;

    Element.prototype.attachShadow = function (init) {
      // Check if we are the new Google places autocomplete element...
      if (this.localName === "gmp-place-autocomplete") {
        // If we are, we need to override the default behaviour of attachShadow() to
        // set the mode to open to allow us to crowbar a style element into the shadow DOM.
        const shadow = attachShadow.call(this, {
          ...init,
          mode: "open"
      });

        const style = document.createElement("style");

        // Apply our own styles to the shadow DOM.
        var inputColor = '#333';
        if ( $(this).closest(".elementor_search_form_2").length && $(this).closest(".elementor_search_form_2").hasClass("type_1") ) {
            inputColor = '#fff';
        }

        style.textContent = `
            :host {
                border: 1px solid transparent !important;
                background: transparent !important;
            }
            gmp-place-autocomplete {
                border: 1px solid transparent !important;
                background: transparent !important;
            }
            .widget-container {
                border: 1px solid transparent !important;
                background: transparent !important;
                position: relative;
                width: 250px !important;
                max-width: 250px !important;
            }
            .autocomplete-icon, .clear-button {
                display: none !important;
            }
            input {
                color: ${inputColor};
                font-size: 14px;
                height: 40px !important;
            }
            .dropdown {
                position: absolute !important;
                top: 40px !important;
                left: 0 !important;
                width: 450px !important;
            }
            @media screen and (max-width: 600px) {
                .widget-container {
                    width: 175px !important;
                }
            }
        `;

        shadow.appendChild(style);

        // Set the shadowRoot property to the new shadow root that has our styles in it.
        return shadow;
    }
      // ...for other elements, proceed with the original behaviour of attachShadow().
    return attachShadow.call(this, init);
    };
})(jQuery);