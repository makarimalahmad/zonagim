import { useEffect, useRef, useState } from "react";

/**
 * Gambar dengan ruang tetap, skeleton, transisi halus, dan fallback.
 * Parent menentukan ukuran melalui wrapperClassName agar layout tidak bergeser.
 */
export default function ProgressiveImage({
    src,
    alt = "",
    width,
    height,
    wrapperClassName = "",
    className = "",
    loading = "lazy",
    decoding = "async",
    fetchPriority = "auto",
    fallback = null,
    onLoad,
    onError,
    ...props
}) {
    const [status, setStatus] = useState(src ? "loading" : "error");
    const imgRef = useRef(null);

    useEffect(() => {
        setStatus(src ? "loading" : "error");
    }, [src]);

    useEffect(() => {
        const img = imgRef.current;
        if (!img || !src) return;

        if (img.complete) {
            setStatus(img.naturalWidth > 0 ? "loaded" : "error");
        }
    }, [src]);

    const handleLoad = (event) => {
        setStatus("loaded");
        onLoad?.(event);
    };

    const handleError = (event) => {
        setStatus("error");
        onError?.(event);
    };

    return (
        <span
            className={`progressive-image ${wrapperClassName}`}
            aria-busy={status === "loading"}
        >
            {status === "loading" && (
                <span
                    className="progressive-image__skeleton"
                    aria-hidden="true"
                />
            )}

            {src && status !== "error" && (
                <img
                    {...props}
                    ref={imgRef}
                    src={src}
                    alt={alt}
                    width={width}
                    height={height}
                    loading={loading}
                    decoding={decoding}
                    fetchPriority={fetchPriority}
                    onLoad={handleLoad}
                    onError={handleError}
                    className={`progressive-image__media ${
                        status === "loaded"
                            ? "progressive-image__media--loaded"
                            : ""
                    } ${className}`}
                />
            )}

            {status === "error" && fallback && (
                <span className="progressive-image__fallback">{fallback}</span>
            )}
        </span>
    );
}