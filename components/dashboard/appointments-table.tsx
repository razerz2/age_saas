import { Clock, CheckCircle, AlertCircle, User, MoreHorizontal } from "lucide-react"

interface Appointment {
  patient: string
  initials: string
  doctor: string
  time: string
  status: "confirmado" | "aguardando" | "cancelado"
  type: string
}

const upcomingAppointments: Appointment[] = [
  {
    patient: "Maria Silva",
    initials: "MS",
    doctor: "Dr. Joao Pereira",
    time: "08:00",
    status: "confirmado",
    type: "Consulta",
  },
  {
    patient: "Carlos Santos",
    initials: "CS",
    doctor: "Dra. Ana Costa",
    time: "08:30",
    status: "confirmado",
    type: "Retorno",
  },
  {
    patient: "Lucia Ferreira",
    initials: "LF",
    doctor: "Dr. Joao Pereira",
    time: "09:00",
    status: "aguardando",
    type: "Consulta",
  },
  {
    patient: "Pedro Oliveira",
    initials: "PO",
    doctor: "Dra. Mariana Lima",
    time: "09:30",
    status: "confirmado",
    type: "Exame",
  },
  {
    patient: "Ana Beatriz",
    initials: "AB",
    doctor: "Dr. Joao Pereira",
    time: "10:00",
    status: "cancelado",
    type: "Consulta",
  },
  {
    patient: "Roberto Mendes",
    initials: "RM",
    doctor: "Dra. Ana Costa",
    time: "10:30",
    status: "aguardando",
    type: "Retorno",
  },
]

const statusConfig = {
  confirmado: {
    label: "Confirmado",
    dotColor: "bg-[hsl(160,60%,42%)]",
    textColor: "text-[hsl(160,60%,42%)]",
    bgColor: "bg-[hsl(160,60%,42%)]/10",
    icon: CheckCircle,
  },
  aguardando: {
    label: "Aguardando",
    dotColor: "bg-[hsl(35,92%,52%)]",
    textColor: "text-[hsl(35,92%,52%)]",
    bgColor: "bg-[hsl(35,92%,52%)]/10",
    icon: Clock,
  },
  cancelado: {
    label: "Cancelado",
    dotColor: "bg-destructive",
    textColor: "text-destructive",
    bgColor: "bg-destructive/10",
    icon: AlertCircle,
  },
}

export function AppointmentsTable() {
  return (
    <div className="overflow-hidden rounded-xl border border-border/60 bg-card">
      <div className="flex items-center justify-between border-b border-border/40 px-6 py-4">
        <div>
          <h3 className="text-sm font-bold text-card-foreground">Proximos Agendamentos</h3>
          <p className="mt-0.5 text-[11px] text-muted-foreground">Agenda do dia de hoje</p>
        </div>
        <a
          href="#"
          className="rounded-lg bg-primary px-3.5 py-1.5 text-[12px] font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
        >
          Ver Todos
        </a>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead>
            <tr className="border-b border-border/40 bg-muted/30">
              <th className="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-[0.06em] text-muted-foreground">
                Paciente
              </th>
              <th className="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-[0.06em] text-muted-foreground">
                Profissional
              </th>
              <th className="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-[0.06em] text-muted-foreground">
                Horario
              </th>
              <th className="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-[0.06em] text-muted-foreground">
                Tipo
              </th>
              <th className="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-[0.06em] text-muted-foreground">
                Status
              </th>
              <th className="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-[0.06em] text-muted-foreground">
                <span className="sr-only">Acoes</span>
              </th>
            </tr>
          </thead>
          <tbody>
            {upcomingAppointments.map((apt, index) => {
              const config = statusConfig[apt.status]
              return (
                <tr
                  key={index}
                  className="group border-b border-border/30 transition-colors last:border-0 hover:bg-muted/30"
                >
                  <td className="px-6 py-3.5">
                    <div className="flex items-center gap-3">
                      <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-[11px] font-bold text-primary">
                        {apt.initials}
                      </div>
                      <span className="text-sm font-medium text-card-foreground">
                        {apt.patient}
                      </span>
                    </div>
                  </td>
                  <td className="px-6 py-3.5">
                    <div className="flex items-center gap-2">
                      <User className="h-3.5 w-3.5 text-muted-foreground" />
                      <span className="text-sm text-muted-foreground">{apt.doctor}</span>
                    </div>
                  </td>
                  <td className="px-6 py-3.5">
                    <span className="inline-flex items-center gap-1.5 rounded-md bg-muted/60 px-2 py-0.5 text-sm font-medium text-card-foreground">
                      <Clock className="h-3 w-3 text-muted-foreground" />
                      {apt.time}
                    </span>
                  </td>
                  <td className="px-6 py-3.5">
                    <span className="text-sm text-muted-foreground">{apt.type}</span>
                  </td>
                  <td className="px-6 py-3.5">
                    <span
                      className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ${config.bgColor} ${config.textColor}`}
                    >
                      <span className={`h-1.5 w-1.5 rounded-full ${config.dotColor}`} />
                      {config.label}
                    </span>
                  </td>
                  <td className="px-6 py-3.5 text-right">
                    <button className="inline-flex h-7 w-7 items-center justify-center rounded-md text-muted-foreground opacity-0 transition-all hover:bg-muted hover:text-foreground group-hover:opacity-100">
                      <MoreHorizontal className="h-4 w-4" />
                    </button>
                  </td>
                </tr>
              )
            })}
          </tbody>
        </table>
      </div>
    </div>
  )
}
