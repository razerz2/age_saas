import { Clock, CheckCircle, AlertCircle, User } from "lucide-react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"

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
    className: "bg-emerald-100 text-emerald-700",
    icon: CheckCircle,
  },
  aguardando: {
    label: "Aguardando",
    className: "bg-amber-100 text-amber-700",
    icon: Clock,
  },
  cancelado: {
    label: "Cancelado",
    className: "bg-red-100 text-red-700",
    icon: AlertCircle,
  },
}

export function AppointmentsTable() {
  return (
    <Card className="border-0 shadow-sm">
      <CardHeader className="flex flex-row items-center justify-between pb-2">
        <CardTitle className="text-lg font-bold text-foreground">
          Proximos Agendamentos
        </CardTitle>
        <a
          href="#"
          className="rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
        >
          Ver Todos
        </a>
      </CardHeader>
      <CardContent className="p-0">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-border bg-muted/30">
                <th className="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                  Paciente
                </th>
                <th className="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                  Profissional
                </th>
                <th className="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                  Horario
                </th>
                <th className="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                  Tipo
                </th>
                <th className="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                  Status
                </th>
              </tr>
            </thead>
            <tbody>
              {upcomingAppointments.map((apt, index) => {
                const config = statusConfig[apt.status]
                return (
                  <tr
                    key={index}
                    className="border-b border-border/50 transition-colors last:border-0 hover:bg-muted/30"
                  >
                    <td className="px-5 py-3.5">
                      <div className="flex items-center gap-3">
                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground shadow-sm">
                          {apt.initials}
                        </div>
                        <span className="text-sm font-medium text-foreground">
                          {apt.patient}
                        </span>
                      </div>
                    </td>
                    <td className="px-5 py-3.5">
                      <div className="flex items-center gap-2">
                        <User className="h-3.5 w-3.5 text-muted-foreground" />
                        <span className="text-sm text-muted-foreground">{apt.doctor}</span>
                      </div>
                    </td>
                    <td className="px-5 py-3.5">
                      <div className="flex items-center gap-2">
                        <Clock className="h-3.5 w-3.5 text-muted-foreground" />
                        <span className="text-sm font-medium text-foreground">{apt.time}</span>
                      </div>
                    </td>
                    <td className="px-5 py-3.5">
                      <span className="text-sm text-muted-foreground">{apt.type}</span>
                    </td>
                    <td className="px-5 py-3.5">
                      <span
                        className={`inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ${config.className}`}
                      >
                        <config.icon className="h-3 w-3" />
                        {config.label}
                      </span>
                    </td>
                  </tr>
                )
              })}
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>
  )
}
