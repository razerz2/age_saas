import { CalendarPlus, UserPlus, FileText, CreditCard } from "lucide-react"

const actions = [
  {
    label: "Novo Agendamento",
    description: "Agendar consulta",
    icon: CalendarPlus,
    iconBg: "bg-[hsl(199,89%,40%)]/10",
    iconColor: "text-[hsl(199,89%,40%)]",
  },
  {
    label: "Novo Paciente",
    description: "Cadastrar paciente",
    icon: UserPlus,
    iconBg: "bg-[hsl(160,60%,42%)]/10",
    iconColor: "text-[hsl(160,60%,42%)]",
  },
  {
    label: "Prontuario",
    description: "Abrir prontuario",
    icon: FileText,
    iconBg: "bg-[hsl(35,92%,52%)]/10",
    iconColor: "text-[hsl(35,92%,52%)]",
  },
  {
    label: "Financeiro",
    description: "Registrar pagamento",
    icon: CreditCard,
    iconBg: "bg-[hsl(199,60%,55%)]/10",
    iconColor: "text-[hsl(199,60%,55%)]",
  },
]

export function QuickActions() {
  return (
    <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
      {actions.map((action) => (
        <button
          key={action.label}
          className="group flex flex-col items-center gap-2.5 rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:border-primary/30 hover:shadow-md"
        >
          <div className={`flex h-11 w-11 items-center justify-center rounded-xl ${action.iconBg} transition-transform duration-200 group-hover:scale-110`}>
            <action.icon className={`h-5 w-5 ${action.iconColor}`} />
          </div>
          <div className="text-center">
            <p className="text-[12px] font-semibold text-card-foreground">{action.label}</p>
            <p className="text-[10px] text-muted-foreground">{action.description}</p>
          </div>
        </button>
      ))}
    </div>
  )
}
