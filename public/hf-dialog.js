/*
| HireFlow's small dialogs, and the one-line messages that follow them.
|
| WHY THESE EXIST
|
| HireFlow asked HR to confirm permanent decisions -- cancelling an interview,
| removing a round, submitting an evaluation that cannot be edited -- through
| window.confirm(), and asked them to TYPE the reason for those decisions into
| window.prompt(). A browser dialog cannot say which applicant it is about,
| cannot show the consequence, cannot be styled, cannot be made safe against a
| stray Return, and on a phone it is a system sheet that covers the page. The
| reason typed into it went straight onto the permanent record with no length
| limit and no way to see what it would be attached to.
|
| So: three functions, one implementation, used everywhere.
|
|   hfConfirm({title, message, detail, confirmLabel, cancelLabel, tone})
|       -> Promise<boolean>
|   hfPrompt({title, message, label, placeholder, value, required, maxLength,
|             multiline, confirmLabel, tone})
|       -> Promise<string|null>        null means cancelled
|   hfToast(message, tone)             'ok' | 'warn' | 'info'
|
| WHAT THEY GUARANTEE
|
|   - focus moves into the dialog and cannot leave it while it is open
|   - Escape and the backdrop cancel; neither can confirm
|   - focus returns to whatever opened the dialog
|   - a destructive confirm never has focus on the destructive button
|   - the dialog is announced as a dialog, and named by its title
|   - it fits a 390px phone
|
| It deliberately does NOT use Bootstrap's modal. Bootstrap's is excellent for
| a form you build in markup; these are built from a call site in JavaScript,
| must return a value to that call site, and must stack above an already-open
| Bootstrap modal -- which is exactly where confirm() was being used.
*/
(function (window, document) {
    'use strict';

    /* Above Bootstrap's modal (1055) so a confirmation can be asked from
       inside one, which is where most of them are asked. */
    var Z = 2000;

    var openCount = 0;

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function uid() {
        return 'hfd-' + Math.random().toString(36).slice(2, 10);
    }

    /*
    | One dialog, built and torn down.
    |
    | `build` fills the body and returns the element that should receive
    | focus; `collect` turns the form into the resolved value, or returns
    | undefined to refuse (a required field left empty).
    */
    function open(opts, build, collect) {
        return new Promise(function (resolve) {
            var opener = document.activeElement;
            var id = uid();

            var back = document.createElement('div');
            back.className = 'hf-dlg-back';
            back.style.zIndex = String(Z + openCount * 2);

            var box = document.createElement('div');
            box.className = 'hf-dlg' + (opts.tone === 'warn' ? ' is-warn' : '');
            box.style.zIndex = String(Z + openCount * 2 + 1);
            box.setAttribute('role', 'alertdialog');
            box.setAttribute('aria-modal', 'true');
            box.setAttribute('aria-labelledby', id + '-t');

            box.innerHTML =
                '<h2 class="hf-dlg-title" id="' + id + '-t">' + esc(opts.title || 'Please confirm') + '</h2>' +
                (opts.message ? '<p class="hf-dlg-msg">' + esc(opts.message) + '</p>' : '') +
                (opts.detail ? '<p class="hf-dlg-detail">' + esc(opts.detail) + '</p>' : '') +
                '<div class="hf-dlg-body"></div>' +
                '<p class="hf-dlg-error" role="alert" hidden></p>' +
                '<div class="hf-dlg-row">' +
                    '<button type="button" class="hf-dlg-btn hf-dlg-cancel">' +
                        esc(opts.cancelLabel || 'Cancel') + '</button>' +
                    '<button type="button" class="hf-dlg-btn hf-dlg-ok">' +
                        esc(opts.confirmLabel || 'Confirm') + '</button>' +
                '</div>';

            var body = box.querySelector('.hf-dlg-body');
            var error = box.querySelector('.hf-dlg-error');
            var cancel = box.querySelector('.hf-dlg-cancel');
            var ok = box.querySelector('.hf-dlg-ok');

            /*
            | An acknowledgement, with nothing to decline.
            |
            | Passing cancelLabel: '' says there is no second option -- "that
            | could not be submitted" is news, not a choice. The button is
            | removed rather than left blank, and Escape still dismisses.
            */
            if (opts.cancelLabel === '' || opts.cancelLabel === false) {
                cancel.remove();
            }

            var preferred = build ? build(body, box) : null;

            document.body.appendChild(back);
            document.body.appendChild(box);
            openCount++;

            function focusables() {
                return Array.prototype.filter.call(
                    box.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'),
                    function (el) { return !el.disabled && el.offsetParent !== null; }
                );
            }

            function close(answer) {
                document.removeEventListener('keydown', onKey, true);
                back.remove();
                box.remove();
                openCount = Math.max(0, openCount - 1);

                /* Back where they were. Without this, dismissing a dialog
                   drops focus on <body> and the next Tab starts from the top
                   of the page. */
                if (opener && typeof opener.focus === 'function' && document.contains(opener)) {
                    opener.focus();
                }

                resolve(answer);
            }

            function confirm() {
                if (!collect) { close(true); return; }

                var value = collect(body, error);

                if (value === undefined) { return; }   // refused; the field said why

                close(value);
            }

            function onKey(e) {
                if (!box.contains(e.target) && e.key !== 'Escape') {
                    /* Focus escaped the dialog -- put it back rather than let
                       the page behind take keystrokes. */
                    e.preventDefault();
                    var f = focusables();
                    if (f.length) { f[0].focus(); }
                    return;
                }

                if (e.key === 'Escape') {
                    e.preventDefault();
                    e.stopPropagation();
                    close(collect ? null : false);
                    return;
                }

                if (e.key !== 'Tab') { return; }

                var list = focusables();
                if (!list.length) { return; }

                var first = list[0];
                var last = list[list.length - 1];

                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }

            cancel.addEventListener('click', function () { close(collect ? null : false); });
            ok.addEventListener('click', confirm);
            back.addEventListener('click', function () { close(collect ? null : false); });
            document.addEventListener('keydown', onKey, true);

            /*
            | What has focus to begin with.
            |
            | An input if there is one, so the person can simply type. Failing
            | that, Cancel -- never the confirm button on a destructive
            | action, because a dialog that opens with "Delete" focused turns
            | a reflexive Return into the thing it was supposed to prevent.
            */
            (preferred
                || (opts.tone === 'warn' && document.contains(cancel) ? cancel : ok)).focus();
        });
    }

    function hfConfirm(opts) {
        opts = opts || {};

        return open(opts, null, null);
    }

    function hfPrompt(opts) {
        opts = opts || {};

        var field;

        return open(
            opts,
            function (body) {
                var id = uid();
                var label = document.createElement('label');
                label.className = 'hf-dlg-label';
                label.setAttribute('for', id);
                label.textContent = opts.label || 'Reason';

                field = document.createElement(opts.multiline ? 'textarea' : 'input');
                field.className = 'hf-dlg-field';
                field.id = id;
                if (!opts.multiline) { field.type = 'text'; }
                if (opts.multiline) { field.rows = 3; }
                if (opts.placeholder) { field.placeholder = opts.placeholder; }
                if (opts.value) { field.value = opts.value; }

                /*
                | A limit the field enforces, matching the column.
                |
                | window.prompt() had none, so a reason longer than the column
                | was silently truncated by the database -- or rejected after
                | the person had typed all of it.
                */
                field.maxLength = opts.maxLength || 300;

                body.appendChild(label);
                body.appendChild(field);

                if (!opts.multiline) {
                    /* Return submits a single-line field, as it would in a form. */
                    field.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            body.parentNode.querySelector('.hf-dlg-ok').click();
                        }
                    });
                }

                return field;
            },
            function (body, error) {
                var value = field.value.trim();

                if (opts.required && value === '') {
                    error.textContent = opts.requiredMessage
                        || 'Please give a reason. It is kept on the record.';
                    error.hidden = false;
                    field.focus();

                    return undefined;   // refuse; the dialog stays open
                }

                return value;
            }
        );
    }

    /*
    | A one-line result.
    |
    | Only ever raised after the server has answered, never before: the point
    | of replacing alert() is not that it was ugly but that a message shown
    | optimistically says something that may not be true.
    */
    function hfToast(message, tone) {
        var host = document.querySelector('.hf-toasts');

        if (!host) {
            host = document.createElement('div');
            host.className = 'hf-toasts';
            document.body.appendChild(host);
        }

        var el = document.createElement('div');
        el.className = 'hf-toast is-' + (tone || 'ok');
        el.setAttribute('role', tone === 'warn' ? 'alert' : 'status');
        el.textContent = message;
        host.appendChild(el);

        /* Long enough to read a sentence; failures stay longer because they
           usually need acting on. */
        window.setTimeout(function () {
            el.classList.add('is-going');
            window.setTimeout(function () { el.remove(); }, 250);
        }, tone === 'warn' ? 6000 : 3500);

        return el;
    }

    /*
    | Confirming a plain form, declaratively.
    |
    | Some confirmations guard an ordinary POST form rather than a fetch --
    | "Cancel this request?", "Issue a new access code?". Those were written
    | as onsubmit="return confirm(...)" and onclick="return confirm(...)",
    | which only works because window.confirm BLOCKS. Nothing promise-based
    | can be dropped into that position.
    |
    | So the form says what to ask and this asks it:
    |
    |   <form data-hf-confirm="Issue a new access code?"
    |         data-hf-detail="Any earlier unused code stops working."
    |         data-hf-ok="Issue code" data-hf-tone="warn">
    |
    | The submit is stopped, the question asked, and the form submitted only
    | on a yes. submit() is called rather than requestSubmit() deliberately:
    | it does not re-fire this handler, so the guard cannot loop.
    */
    document.addEventListener('submit', function (e) {
        var form = e.target;

        if (!form || !form.matches || !form.matches('form[data-hf-confirm]')) { return; }
        if (form.dataset.hfConfirmed === '1') { return; }

        e.preventDefault();

        hfConfirm({
            title: form.dataset.hfConfirm,
            message: form.dataset.hfMessage || '',
            detail: form.dataset.hfDetail || '',
            confirmLabel: form.dataset.hfOk || 'Confirm',
            tone: form.dataset.hfTone || ''
        }).then(function (yes) {
            if (!yes) { return; }

            form.dataset.hfConfirmed = '1';
            form.submit();
        });
    }, true);

    window.hfConfirm = hfConfirm;
    window.hfPrompt = hfPrompt;
    window.hfToast = hfToast;
})(window, document);
