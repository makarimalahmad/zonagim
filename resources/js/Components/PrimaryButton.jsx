export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            className={
                `inline-flex items-center rounded-md border border-transparent bg-neutral px-4 py-2 text-xs font-semibold uppercase tracking-widest text-neutral-content transition duration-150 ease-in-out hover:bg-neutral/90 focus:bg-neutral/90 focus:outline-none focus:ring-2 focus:ring-base-content/30 focus:ring-offset-2 active:bg-neutral ${
                    disabled && 'opacity-25'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
