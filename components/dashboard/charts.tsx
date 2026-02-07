"use client"

import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  XAxis,
  YAxis,
  CartesianGrid,
  ResponsiveContainer,
} from "recharts"
import {
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
} from "@/components/ui/chart"
import { TrendingUp } from "lucide-react"

const appointmentsData = [
  { month: "Jan", agendadas: 120, realizadas: 108, canceladas: 12 },
  { month: "Fev", agendadas: 135, realizadas: 122, canceladas: 13 },
  { month: "Mar", agendadas: 148, realizadas: 140, canceladas: 8 },
  { month: "Abr", agendadas: 142, realizadas: 130, canceladas: 12 },
  { month: "Mai", agendadas: 155, realizadas: 145, canceladas: 10 },
  { month: "Jun", agendadas: 168, realizadas: 160, canceladas: 8 },
  { month: "Jul", agendadas: 175, realizadas: 165, canceladas: 10 },
  { month: "Ago", agendadas: 190, realizadas: 178, canceladas: 12 },
  { month: "Set", agendadas: 185, realizadas: 172, canceladas: 13 },
  { month: "Out", agendadas: 200, realizadas: 188, canceladas: 12 },
  { month: "Nov", agendadas: 210, realizadas: 198, canceladas: 12 },
  { month: "Dez", agendadas: 195, realizadas: 180, canceladas: 15 },
]

const revenueData = [
  { month: "Jan", faturamento: 6200 },
  { month: "Fev", faturamento: 7100 },
  { month: "Mar", faturamento: 7800 },
  { month: "Abr", faturamento: 7500 },
  { month: "Mai", faturamento: 8100 },
  { month: "Jun", faturamento: 8900 },
  { month: "Jul", faturamento: 9200 },
  { month: "Ago", faturamento: 9800 },
  { month: "Set", faturamento: 9500 },
  { month: "Out", faturamento: 10200 },
  { month: "Nov", faturamento: 10800 },
  { month: "Dez", faturamento: 8240 },
]

const CHART_COLORS = {
  agendadas: "hsl(199, 89%, 40%)",
  realizadas: "hsl(160, 60%, 42%)",
  canceladas: "hsl(0, 72%, 55%)",
  faturamento: "hsl(199, 89%, 40%)",
}

export function AppointmentsChart() {
  return (
    <div className="overflow-hidden rounded-xl border border-border/60 bg-card">
      <div className="flex items-center justify-between border-b border-border/40 px-6 py-4">
        <div>
          <h3 className="text-sm font-bold text-card-foreground">Consultas por Mes</h3>
          <p className="mt-0.5 text-[11px] text-muted-foreground">Desempenho anual de agendamentos</p>
        </div>
        <div className="flex items-center gap-4">
          <div className="flex items-center gap-1.5">
            <span className="h-2.5 w-2.5 rounded-full" style={{ background: CHART_COLORS.agendadas }} />
            <span className="text-[11px] text-muted-foreground">Agendadas</span>
          </div>
          <div className="flex items-center gap-1.5">
            <span className="h-2.5 w-2.5 rounded-full" style={{ background: CHART_COLORS.realizadas }} />
            <span className="text-[11px] text-muted-foreground">Realizadas</span>
          </div>
          <div className="flex items-center gap-1.5">
            <span className="h-2.5 w-2.5 rounded-full" style={{ background: CHART_COLORS.canceladas }} />
            <span className="text-[11px] text-muted-foreground">Canceladas</span>
          </div>
        </div>
      </div>
      <div className="p-4 pt-2">
        <ChartContainer
          config={{
            agendadas: { label: "Agendadas", color: CHART_COLORS.agendadas },
            realizadas: { label: "Realizadas", color: CHART_COLORS.realizadas },
            canceladas: { label: "Canceladas", color: CHART_COLORS.canceladas },
          }}
          className="h-[280px] w-full"
        >
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={appointmentsData} barGap={1} barCategoryGap="25%">
              <CartesianGrid strokeDasharray="3 3" stroke="hsl(214, 14%, 90%)" strokeOpacity={0.5} vertical={false} />
              <XAxis
                dataKey="month"
                tick={{ fontSize: 11, fill: "hsl(215, 12%, 50%)" }}
                axisLine={false}
                tickLine={false}
              />
              <YAxis
                tick={{ fontSize: 11, fill: "hsl(215, 12%, 50%)" }}
                axisLine={false}
                tickLine={false}
                width={35}
              />
              <ChartTooltip content={<ChartTooltipContent />} />
              <Bar dataKey="agendadas" fill={CHART_COLORS.agendadas} radius={[4, 4, 0, 0]} />
              <Bar dataKey="realizadas" fill={CHART_COLORS.realizadas} radius={[4, 4, 0, 0]} />
              <Bar dataKey="canceladas" fill={CHART_COLORS.canceladas} radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </ChartContainer>
      </div>
    </div>
  )
}

export function RevenueChart() {
  const total = revenueData.reduce((sum, d) => sum + d.faturamento, 0)
  return (
    <div className="overflow-hidden rounded-xl border border-border/60 bg-card">
      <div className="flex items-center justify-between border-b border-border/40 px-6 py-4">
        <div>
          <h3 className="text-sm font-bold text-card-foreground">Faturamento Mensal</h3>
          <p className="mt-0.5 text-[11px] text-muted-foreground">Receita total no periodo</p>
        </div>
        <div className="flex items-center gap-2 rounded-lg bg-[hsl(160,60%,42%)]/10 px-2.5 py-1">
          <TrendingUp className="h-3.5 w-3.5 text-[hsl(160,60%,42%)]" />
          <span className="text-xs font-bold text-[hsl(160,60%,42%)]">
            R$ {(total / 1000).toFixed(1)}k total
          </span>
        </div>
      </div>
      <div className="p-4 pt-2">
        <ChartContainer
          config={{
            faturamento: { label: "Faturamento", color: CHART_COLORS.faturamento },
          }}
          className="h-[280px] w-full"
        >
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={revenueData}>
              <defs>
                <linearGradient id="faturamentoGradient" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stopColor={CHART_COLORS.faturamento} stopOpacity={0.2} />
                  <stop offset="100%" stopColor={CHART_COLORS.faturamento} stopOpacity={0} />
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" stroke="hsl(214, 14%, 90%)" strokeOpacity={0.5} vertical={false} />
              <XAxis
                dataKey="month"
                tick={{ fontSize: 11, fill: "hsl(215, 12%, 50%)" }}
                axisLine={false}
                tickLine={false}
              />
              <YAxis
                tick={{ fontSize: 11, fill: "hsl(215, 12%, 50%)" }}
                axisLine={false}
                tickLine={false}
                width={35}
                tickFormatter={(value) => `${(value / 1000).toFixed(0)}k`}
              />
              <ChartTooltip content={<ChartTooltipContent />} />
              <Area
                type="monotone"
                dataKey="faturamento"
                stroke={CHART_COLORS.faturamento}
                strokeWidth={2}
                fill="url(#faturamentoGradient)"
              />
            </AreaChart>
          </ResponsiveContainer>
        </ChartContainer>
      </div>
    </div>
  )
}
