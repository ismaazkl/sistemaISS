import { cn } from '@/lib/utils';

/**
 * Institutional seal used as branding inside the content headers.
 *
 * It is decorative, so it is hidden from assistive technologies. It renders
 * inline (never as a floating overlay) to keep it from covering controls.
 */
export default function Sello({ className }: { className?: string }) {
    return (
        <img
            src="/images/sello.jpeg"
            alt=""
            aria-hidden="true"
            draggable={false}
            className={cn(
                'size-8 shrink-0 rounded-full bg-white object-cover ring-1 ring-black/10 select-none dark:ring-white/15',
                className,
            )}
        />
    );
}
