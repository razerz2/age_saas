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
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
} from "@/components/ui/chart"

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

export function AppointmentsChart() {
  return (
    <Card className="border-0 shadow-sm">
      <CardHeader className="pb-2">
        <CardTitle className="text-lg font-bold text-foreground">
          Consultas por Mes
        </CardTitle>
      </CardHeader>
      <CardContent>
        <ChartContainer
          config={{
            agendadas: {
              label: "Agendadas",
              color: "#3b82f6",
            },
            realizadas: {
              label: "Realizadas",
              color: "#10b981",
            },
            canceladas: {
              label: "Canceladas",
              color: "#ef4444",
            },
          }}
          className="h-[300px] w-full"
        >
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={appointmentsData} barGap={2} barCategoryGap="20%">
              <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
              <XAxis
                dataKey="month"
                tick={{ fontSize: 12 }}
                axisLine={false}
                tickLine={false}
              />
              <YAxis tick={{ fontSize: 12 }} axisLine={false} tickLine={false} />
              <ChartTooltip content={<ChartTooltipContent />} />
              <Bar dataKey="agendadas" fill="#3b82f6" radius={[4, 4, 0, 0]} />
              <Bar dataKey="realizadas" fill="#10b981" radius={[4, 4, 0, 0]} />
              <Bar dataKey="canceladas" fill="#ef4444" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </ChartContainer>
        <div className="mt-3 flex items-center justify-center gap-6">
          <div className="flex items-center gap-2">
            <div className="h-3 w-3 rounded-sm" style={{ background: "#3b82f6" }} />
            <span className="text-xs text-muted-foreground">Agendadas</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="h-3 w-3 rounded-sm" style={{ background: "#10b981" }} />
            <span className="text-xs text-muted-foreground">Realizadas</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="h-3 w-3 rounded-sm" style={{ background: "#ef4444" }} />
            <span className="text-xs text-muted-foreground">Canceladas</span>
          </div>
        </div>
      </CardContent>
    </Card>
  )
}

export function RevenueChart() {
  return (
    <Card className="border-0 shadow-sm">
      <CardHeader className="pb-2">
        <CardTitle className="text-lg font-bold text-foreground">
          Faturamento Mensal (R$)
        </CardTitle>
      </CardHeader>
      <CardContent>
        <ChartContainer
          config={{
            faturamento: {
              label: "Faturamento",
              color: "#3b82f6",
            },
          }}
          className="h-[300px] w-full"
        >
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={revenueData}>
              <defs>
                <linearGradient id="faturamentoGradient" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#3b82f6" stopOpacity={0.3} />
                  <stop offset="95%" stopColor="#3b82f6" stopOpacity={0} />
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
              <XAxis
                dataKey="month"
                tick={{ fontSize: 12 }}
                axisLine={false}
                tickLine={false}
              />
              <YAxis
                tick={{ fontSize: 12 }}
                axisLine={false}
                tickLine={false}
                tickFormatter={(value) => `${(value / 1000).toFixed(0)}k`}
              />
              <ChartTooltip
                content={<ChartTooltipContent />}
              />
              <Area
                type="monotone"
                dataKey="faturamento"
                stroke="#3b82f6"
                strokeWidth={2.5}
                fill="url(#faturamentoGradient)"
              />
            </AreaChart>
          </ResponsiveContainer>
        </ChartContainer>
      </CardContent>
    </Card>
  )
}
