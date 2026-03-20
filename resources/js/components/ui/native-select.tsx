import * as React from 'react';
import { cn } from '@/lib/utils';

type NativeSelectProps = React.ComponentPropsWithoutRef<'select'>;

export function NativeSelect({ className, ...props }: NativeSelectProps) {
    return (
        <select
            className={cn(
                'border-input focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-[3px]',
                className,
            )}
            {...props}
        />
    );
}
