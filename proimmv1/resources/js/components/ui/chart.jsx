import {
    Area,
    AreaChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
} from 'recharts';
import { cn } from '../../lib/utils';

export function ChartContainer({ className, children }) {
    return <div className={cn('w-full overflow-hidden rounded-2xl border border-[#c8d4de] bg-[#f7fbfe]', className)}>{children}</div>;
}

export function ChartLegend({ items = [] }) {
    return (
        <div className="flex flex-wrap items-center gap-4 border-t border-[#c8d4de] px-4 py-3 text-xs text-[#5f7182]">
            {items.map((item) => (
                <div key={item.label} className="flex items-center gap-2">
                    <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: item.color }} />
                    <span>{item.label}</span>
                </div>
            ))}
        </div>
    );
}

function ChartTooltipContent({ active, payload }) {
    if (!active || !payload?.length) return null;

    const item = payload[0];
    const value = Number(item.value ?? 0);
    const previousValue = Number(item.payload.prevValue ?? 0);
    const delta = value - previousValue;
    const deltaPercent = previousValue ? Math.round((delta / previousValue) * 100) : null;
    const deltaLabel =
        previousValue === 0
            ? 'Premier point de la série'
            : `${delta >= 0 ? '+' : ''}${new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(delta)} FCFA`;

    return (
        <div className="rounded-2xl border border-[#c8d4de] bg-white px-4 py-3 shadow-lg">
            <p className="text-xs font-medium uppercase tracking-[0.24em] text-[#5f7182]">{item.payload.label}</p>
            <p className="mt-2 text-lg font-semibold text-[#0f172a]">
                {new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value)} FCFA
            </p>
            <div className="mt-2 flex items-center gap-2 text-xs text-[#5f7182]">
                <span className={delta >= 0 ? 'text-[#4d8500]' : 'text-[#b42318]'}>
                    {deltaPercent !== null ? `${delta >= 0 ? '+' : ''}${deltaPercent}%` : deltaLabel}
                </span>
                {deltaPercent !== null ? <span>vs mois précédent</span> : null}
            </div>
        </div>
    );
}

export function ChartArea({
    data = [],
    dataKey = 'value',
    labelKey = 'label',
    color = '#00559b',
    className,
}) {
    const chartData = data.map((item) => ({
        ...item,
        [dataKey]: Number(item[dataKey]) || 0,
        [labelKey]: item[labelKey],
        prevValue: 0,
    }));

    chartData.forEach((item, index) => {
        item.prevValue = index > 0 ? chartData[index - 1][dataKey] : 0;
    });

    return (
        <div className={cn('relative w-full px-2 py-4 sm:px-4', className)}>
            <div className="h-[280px] w-full">
                <ResponsiveContainer width="100%" height="100%">
                    <AreaChart data={chartData} margin={{ top: 8, right: 8, left: 0, bottom: 0 }}>
                        <defs>
                            <linearGradient id="revenueGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stopColor={color} stopOpacity="0.28" />
                                <stop offset="100%" stopColor={color} stopOpacity="0.02" />
                            </linearGradient>
                        </defs>

                        <CartesianGrid stroke="#c8d4de" strokeDasharray="4 6" vertical={false} />
                        <XAxis
                            dataKey={labelKey}
                            axisLine={false}
                            tickLine={false}
                            tickMargin={14}
                            tick={{ fill: '#5f7182', fontSize: 12 }}
                        />
                        <Tooltip
                            cursor={{ stroke: '#00559b', strokeDasharray: '4 4' }}
                            content={<ChartTooltipContent />}
                        />
                        <Area
                            type="monotone"
                            dataKey={dataKey}
                            stroke={color}
                            strokeWidth={3}
                            fill="url(#revenueGradient)"
                            dot={{ r: 4.5, fill: '#ffffff', stroke: color, strokeWidth: 3 }}
                            activeDot={{ r: 6, stroke: color, strokeWidth: 3, fill: '#ffffff' }}
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}
