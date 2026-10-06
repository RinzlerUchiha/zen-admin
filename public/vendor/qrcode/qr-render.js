/*
| Drawing a QR code onto a canvas.
|
| The vendored library computes modules and touches no DOM, so all rendering is
| ours. That keeps third-party code away from the page and means the only thing
| the library ever sees is the URL string.
|
| Canvas rather than an <img> or innerHTML: nothing is parsed as markup, and the
| QR can be redrawn at any size without a round trip.
*/
(function (window) {
    'use strict';

    /* Error correction M: reliable on a phone camera at arm's length without
       making the code needlessly dense. */
    var ERROR_CORRECTION = 'M';

    /* 0 lets the library pick the smallest version that fits the URL. */
    var AUTO_VERSION = 0;

    /**
     * Draw `text` as a QR code into the given canvas.
     *
     * @param {HTMLCanvasElement} canvas
     * @param {string} text            what the QR encodes — here, an entry URL
     * @param {number} [size]          pixel width/height; defaults to the canvas's
     * @returns {boolean}              false if it could not be drawn
     */
    function draw(canvas, text, size) {
        if (!canvas || !canvas.getContext || typeof window.qrcode !== 'function') {
            return false;
        }

        if (typeof text !== 'string' || text === '') {
            return false;
        }

        var qr;

        try {
            qr = window.qrcode(AUTO_VERSION, ERROR_CORRECTION);
            qr.addData(text);
            qr.make();
        } catch (err) {
            // A URL too long for any QR version, or a bad argument. The caller
            // shows the link instead; a half-drawn code would be worse than none.
            return false;
        }

        var modules = qr.getModuleCount();
        var target = size || canvas.width || 160;

        /* Whole pixels per module, or the code blurs and stops scanning. */
        var scale = Math.max(1, Math.floor(target / (modules + 2)));
        var quiet = scale;                       // a 1-module quiet border
        var side = modules * scale + quiet * 2;

        canvas.width = side;
        canvas.height = side;

        var ctx = canvas.getContext('2d');

        /* The quiet zone must be light, not transparent — a dark page behind a
           transparent margin makes the code unreadable. */
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, side, side);

        ctx.fillStyle = '#000000';

        for (var row = 0; row < modules; row++) {
            for (var col = 0; col < modules; col++) {
                if (qr.isDark(row, col)) {
                    ctx.fillRect(quiet + col * scale, quiet + row * scale, scale, scale);
                }
            }
        }

        /* So screen readers and tests can tell what it points at. */
        canvas.setAttribute('role', 'img');
        canvas.setAttribute('aria-label', 'QR code for ' + text);
        canvas.dataset.qrText = text;

        return true;
    }

    window.hfQr = { draw: draw };
})(window);
