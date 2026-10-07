/**
 * Institutional seal rendered as a fixed watermark on the top left corner of
 * every view.
 *
 * It is decorative, so it is hidden from assistive technologies, and it never
 * captures pointer events in order to avoid blocking the controls underneath.
 */
export default function Sello({ className = '' }: { className?: string }) {
    return (
        <img
            src="/images/sello.jpeg"
            alt=""
            aria-hidden="true"
            draggable={false}
            className={`pointer-events-none fixed top-4 left-4 z-40 size-14 rounded-full object-cover opacity-90 shadow-sm ring-1 ring-black/10 select-none md:size-16 ${className}`}
        />
    );
}
