import {
  CalendarCheck,
  Users,
  DollarSign,
  Stethoscope,
  TrendingUp,
  TrendingDown,
} from "lucide-react"

const stats = [
  {
    label: "Consultas Hoje",
    value: "14",
    variation: "+12%",
    trend: "up" as const,
    icon: CalendarCheck,
    gradient: "from-blue-500 to-blue-600",
    bgGradient: "from-blue-500/10 to-blue-600/10",
    shadowColor: "shadow-blue-500/25",
  },
  {
    label: "Pacientes Ativos",
    value: "85",
    variation: "+8%",
    trend: "up" as const,
    icon: Users,
    gradient: "from-emerald-500 to-emerald-600",
    bgGradient: "from-emerald-500/10 to-emerald-600/10",
    shadowColor: "shadow-emerald-500/25",
  },
  {
    label: "Faturamento",
    value: "R$ 8.240",
    variation: "+23%",
    trend: "up" as const,
    icon: DollarSign,
    gradient: "from-amber-500 to-orange-500",
    bgGradient: "from-amber-500/10 to-orange-500/10",
    shadowColor: "shadow-amber-500/25",
  },
  {
    label: "Profissionais",
    value: "12",
    variation: "-2%",
    trend: "down" as const,
    icon: Stethoscope,
    gradient: "from-sky-500 to-blue-500",
    bgGradient: "from-sky-500/10 to-blue-500/10",
    shadowColor: "shadow-sky-500/25",
  },
  {
    label: "Agendamentos Mes",
    value: "342",
    variation: "+15%",
    trend: "up" as const,
    icon: CalendarCheck,
    gradient: "from-indigo-500 to-indigo-600",
    bgGradient: "from-indigo-500/10 to-indigo-600/10",
    shadowColor: "shadow-indigo-500/25",
  },
  {
    label: "Taxa Presenca",
    value: "92%",
    variation: "+3%",
    trend: "up" as const,
    icon: TrendingUp,
    gradient: "from-emerald-500 to-teal-500",
    bgGradient: "from-emerald-500/10 to-teal-500/10",
    shadowColor: "shadow-emerald-500/25",
  },
]

export function StatCards() {
  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {stats.map((stat) => (
        <div
          key={stat.label}
          className={`group relative overflow-hidden rounded-xl border-0 bg-gradient-to-br ${stat.bgGradient} p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg`}
        >
          <div className="flex items-start justify-between">
            <div className="flex-1">
              <p className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                {stat.label}
              </p>
              <p className="mt-2 text-3xl font-extrabold tracking-tight text-foreground">
                {stat.value}
              </p>
              <div className="mt-2 flex items-center gap-1">
                {stat.trend === "up" ? (
                  <TrendingUp className="h-3.5 w-3.5 text-emerald-500" />
                ) : (
                  <TrendingDown className="h-3.5 w-3.5 text-destructive" />
                )}
                <span
                  className={`text-xs font-bold ${
                    stat.trend === "up" ? "text-emerald-500" : "text-destructive"
                  }`}
                >
                  {stat.variation}
                </span>
                <span className="text-xs text-muted-foreground">vs mes anterior</span>
              </div>
            </div>
            <div
              className={`flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br ${stat.gradient} ${stat.shadowColor} shadow-lg transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3`}
            >
              <stat.icon className="h-7 w-7 text-white" />
            </div>
          </div>
        </div>
      ))}
    </div>
  )
}
