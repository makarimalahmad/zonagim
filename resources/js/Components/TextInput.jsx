import { forwardRef, useEffect, useImperativeHandle, useRef } from 'react';

export default forwardRef(function TextInput(
    { type = 'text', className = '', isFocused = false, ...props },
    ref,
) {
    const localRef = useRef(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    return (
        <input
            {...props}
            type={type}
            className={
                'w-full rounded-lg border border-base-300 bg-base-200/50 text-base-content placeholder-base-content/40 shadow-sm focus:border-yellow-500 focus:bg-base-200 focus:ring-1 focus:ring-yellow-500 transition-all duration-200 py-3 px-4 mt-2 ' +
                className
            }
            ref={localRef}
        />
    );
});
