import { CalendarCheck, UserPlus, CreditCard, FileText, Clock } from "lucide-react"

const activities = [
  {
    icon: CalendarCheck,
    iconBg: "bg-[hsl(199,89%,40%)]/10",
    iconColor: "text-[hsl(199,89%,40%)]",
    title: "Consulta confirmada",
    description: "Maria Silva confirmou para 08:00",
    time: "2 min",
  },
  {
    icon: UserPlus,
    iconBg: "bg-[hsl(160,60%,42%)]/10",
    iconColor: "text-[hsl(160,60%,42%)]",
    title: "Novo paciente cadastrado",
    description: "Rafael Gomes - Cardiologia",
    time: "15 min",
  },
  {
    icon: CreditCard,
    iconBg: "bg-[hsl(35,92%,52%)]/10",
    iconColor: "text-[hsl(35,92%,52%)]",
    title: "Pagamento recebido",
    description: "Fatura #1042 - R$ 350,00",
    time: "1h",
  },
  {
    icon: FileText,
    iconBg: "bg-[hsl(199,60%,55%)]/10",
    iconColor: "text-[hsl(199,60%,55%)]",
    title: "Prontuario atualizado",
    description: "Dr. Joao Pereira - Paciente Carlos S.",
    time: "2h",
  },
  {
    icon: CalendarCheck,
    iconBg: "bg-destructive/10",
    iconColor: "text-destructive",
    title: "Consulta cancelada",
    description: "Ana Beatriz cancelou as 10:00",
    time: "3h",
  },
]

export function ActivityFeed() {
  return (
    <div className="overflow-hidden rounded-xl border border-border/60 bg-card">
      <div className="flex items-center justify-between border-b border-border/40 px-6 py-4">
        <div>
          <h3 className="text-sm font-bold text-card-foreground">Atividade Recente</h3>
          <p className="mt-0.5 text-[11px] text-muted-foreground">Ultimas acoes do sistema</p>
        </div>
      </div>
      <div className="divide-y divide-border/30">
        {activities.map((activity, i) => (
          <div key={i} className="flex items-start gap-3 px-6 py-3.5 transition-colors hover:bg-muted/20">
            <div className={`mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${activity.iconBg}`}>
              <activity.icon className={`h-4 w-4 ${activity.iconColor}`} />
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-[13px] font-medium text-card-foreground">{activity.title}</p>
              <p className="text-[11px] text-muted-foreground">{activity.description}</p>
            </div>
            <div className="flex shrink-0 items-center gap-1 text-muted-foreground">
              <Clock className="h-3 w-3" />
              <span className="text-[10px]">{activity.time}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
