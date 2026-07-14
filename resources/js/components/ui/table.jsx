import { cn } from '../../lib/utils';

function Table({ className, ...props }) {
    return <table className={cn('w-full caption-bottom text-sm', className)} {...props} />;
}

function TableHeader({ className, ...props }) {
    return <thead className={cn('[&_tr]:border-b', className)} {...props} />;
}

function TableBody({ className, ...props }) {
    return <tbody className={cn('[&_tr:last-child]:border-0', className)} {...props} />;
}

function TableFooter({ className, ...props }) {
    return (
        <tfoot className={cn('border-t bg-slate-50 font-medium [&>tr]:last:border-b-0', className)} {...props} />
    );
}

function TableRow({ className, ...props }) {
    return (
        <tr className={cn('border-b border-slate-200 transition-colors hover:bg-slate-50/60', className)} {...props} />
    );
}

function TableHead({ className, ...props }) {
    return (
        <th
            className={cn(
                'h-12 px-6 text-left align-middle text-xs font-medium uppercase tracking-[0.2em] text-slate-500',
                className
            )}
            {...props}
        />
    );
}

function TableCell({ className, ...props }) {
    return <td className={cn('px-6 py-4 align-middle', className)} {...props} />;
}

function TableCaption({ className, ...props }) {
    return <caption className={cn('mt-4 text-sm text-slate-500', className)} {...props} />;
}

export {
    Table,
    TableHeader,
    TableBody,
    TableFooter,
    TableHead,
    TableRow,
    TableCell,
    TableCaption,
};
