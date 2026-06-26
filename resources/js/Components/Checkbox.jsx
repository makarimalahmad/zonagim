export default function Checkbox({ className = "", ...props }) {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                "w-4 h-4 rounded border-base-content/30 bg-base-100 text-yellow-500 shadow-sm focus:ring-yellow-500 focus:ring-offset-0 focus:ring-2 checked:bg-yellow-500 checked:border-yellow-500 cursor-pointer transition-colors " +
                className
            }
        />
    );
}

