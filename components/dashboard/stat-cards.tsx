import {
  CalendarCheck,
  Users,
  DollarSign,
  Stethoscope,
  TrendingUp,
  TrendingDown,
  CalendarDays,
  Target,
} from "lucide-react"

const stats = [
  {
    label: "Consultas Hoje",
    value: "14",
    variation: "+12%",
    trend: "up" as const,
    description: "vs ontem",
    icon: CalendarCheck,
    iconBg: "bg-[hsl(199,89%,40%)]/10",
    iconColor: "text-[hsl(199,89%,40%)]",
  },
  {
    label: "Pacientes Ativos",
    value: "85",
    variation: "+8%",
    trend: "up" as const,
    description: "vs mes anterior",
    icon: Users,
    iconBg: "bg-[hsl(160,60%,42%)]/10",
    iconColor: "text-[hsl(160,60%,42%)]",
  },
  {
    label: "Faturamento Mensal",
    value: "R$ 8.240",
    variation: "+23%",
    trend: "up" as const,
    description: "vs mes anterior",
    icon: DollarSign,
    iconBg: "bg-[hsl(35,92%,52%)]/10",
    iconColor: "text-[hsl(35,92%,52%)]",
  },
  {
    label: "Profissionais",
    value: "12",
    variation: "-2%",
    trend: "down" as const,
    description: "vs mes anterior",
    icon: Stethoscope,
    iconBg: "bg-[hsl(199,89%,40%)]/10",
    iconColor: "text-[hsl(199,89%,40%)]",
  },
  {
    label: "Agendamentos / Mes",
    value: "342",
    variation: "+15%",
    trend: "up" as const,
    description: "vs mes anterior",
    icon: CalendarDays,
    iconBg: "bg-[hsl(160,60%,42%)]/10",
    iconColor: "text-[hsl(160,60%,42%)]",
  },
  {
    label: "Taxa de Presenca",
    value: "92%",
    variation: "+3%",
    trend: "up" as const,
    description: "vs mes anterior",
    icon: Target,
    iconBg: "bg-[hsl(199,89%,40%)]/10",
    iconColor: "text-[hsl(199,89%,40%)]",
  },
]

export function StatCards() {
  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
      {stats.map((stat) => (
        <div
          key={stat.label}
          className="group relative overflow-hidden rounded-xl border border-border/60 bg-card p-5 transition-all duration-200 hover:border-border hover:shadow-md"
        >
          <div className="flex items-start justify-between">
            <div className="flex-1">
              <p className="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                {stat.label}
              </p>
              <p className="mt-2 text-3xl font-extrabold tracking-tight text-card-foreground">
                {stat.value}
              </p>
              <div className="mt-2 flex items-center gap-1.5">
                <span
                  className={`inline-flex items-center gap-0.5 rounded-md px-1.5 py-0.5 text-[11px] font-bold ${
                    stat.trend === "up"
                      ? "bg-[hsl(160,60%,42%)]/10 text-[hsl(160,60%,42%)]"
                      : "bg-destructive/10 text-destructive"
                  }`}
                >
                  {stat.trend === "up" ? (
                    <TrendingUp className="h-3 w-3" />
                  ) : (
                    <TrendingDown className="h-3 w-3" />
                  )}
                  {stat.variation}
                </span>
                <span className="text-[11px] text-muted-foreground">{stat.description}</span>
              </div>
            </div>
            <div
              className={`flex h-12 w-12 items-center justify-center rounded-xl ${stat.iconBg} transition-transform duration-200 group-hover:scale-105`}
            >
              <stat.icon className={`h-6 w-6 ${stat.iconColor}`} />
            </div>
          </div>
        </div>
      ))}
    </div>
  )
}
