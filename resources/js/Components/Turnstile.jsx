import {
    useEffect,
    useRef,
    forwardRef,
    useImperativeHandle,
} from "react";

/**
 * Cloudflare Turnstile Component - Stable version
 * @param {string} siteKey - The Turnstile site key
 * @param {function} onVerify - Callback when verification succeeds, receives token
 * @param {function} onError - Callback when verification fails
 * @param {function} onExpire - Callback when token expires
 * @param {string} theme - 'light', 'dark', or 'auto'
 *
 * Exposes an imperative `reset()` via ref so parent forms can request a fresh
 * token. Turnstile tokens are single-use; after a failed form submit the old
 * token is consumed, so the widget must be reset to issue a new one.
 */
const Turnstile = forwardRef(function Turnstile(
    { siteKey, onVerify, onError, onExpire, theme = "dark", className = "" },
    ref,
) {
    const containerRef = useRef(null);
    const widgetIdRef = useRef(null);
    const isRenderedRef = useRef(false);

    // Store callbacks in refs to avoid re-renders
    const onVerifyRef = useRef(onVerify);
    const onErrorRef = useRef(onError);
    const onExpireRef = useRef(onExpire);

    // Update refs when callbacks change
    useEffect(() => {
        onVerifyRef.current = onVerify;
        onErrorRef.current = onError;
        onExpireRef.current = onExpire;
    }, [onVerify, onError, onExpire]);

    // Allow the parent to reset the widget (e.g. after a failed submit) so a
    // fresh token is generated and the submit button becomes usable again.
    useImperativeHandle(ref, () => ({
        reset: () => {
            if (widgetIdRef.current && window.turnstile) {
                try {
                    window.turnstile.reset(widgetIdRef.current);
                } catch (e) {
                    // ignore reset errors
                }
            }
        },
    }));

    useEffect(() => {
        // Prevent multiple renders
        if (isRenderedRef.current) return;

        // Load Turnstile script if not already loaded
        const scriptId = "cf-turnstile-script";
        let script = document.getElementById(scriptId);

        const renderWidget = () => {
            if (
                containerRef.current &&
                window.turnstile &&
                !widgetIdRef.current &&
                !isRenderedRef.current
            ) {
                try {
                    isRenderedRef.current = true;
                    widgetIdRef.current = window.turnstile.render(
                        containerRef.current,
                        {
                            sitekey: siteKey,
                            theme: theme,
                            callback: (token) => {
                                if (onVerifyRef.current)
                                    onVerifyRef.current(token);
                            },
                            "error-callback": () => {
                                if (onErrorRef.current) onErrorRef.current();
                            },
                            "expired-callback": () => {
                                if (onExpireRef.current) onExpireRef.current();
                            },
                        },
                    );
                } catch (e) {
                    console.error("Turnstile render error:", e);
                    isRenderedRef.current = false;
                }
            }
        };

        if (!script) {
            script = document.createElement("script");
            script.id = scriptId;
            script.src =
                "https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit";
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
            script.addEventListener("load", renderWidget);
        } else if (window.turnstile) {
            // Script already loaded, render immediately
            renderWidget();
        } else {
            // Script is loading, wait for it
            script.addEventListener("load", renderWidget);
        }

        return () => {
            // Cleanup widget on unmount
            if (widgetIdRef.current && window.turnstile) {
                try {
                    window.turnstile.remove(widgetIdRef.current);
                } catch (e) {
                    // Ignore cleanup errors
                }
                widgetIdRef.current = null;
                isRenderedRef.current = false;
            }
        };
    }, [siteKey, theme]); // Only re-run if siteKey or theme changes

    return <div ref={containerRef} className={`cf-turnstile ${className}`} />;
});

export default Turnstile;
