import { Clock, Users, Stethoscope } from "lucide-react"

interface Office {
  name: string
  specialty: string
  patients: number
  maxPatients: number
  nextSlot: string
  status: "Disponivel" | "Ocupado" | "Intervalo"
}

const offices: Office[] = [
  {
    name: "Dr. Joao Pereira",
    specialty: "Cardiologia",
    patients: 6,
    maxPatients: 8,
    nextSlot: "10:30",
    status: "Ocupado",
  },
  {
    name: "Dra. Ana Costa",
    specialty: "Dermatologia",
    patients: 4,
    maxPatients: 7,
    nextSlot: "09:00",
    status: "Disponivel",
  },
  {
    name: "Dra. Mariana Lima",
    specialty: "Ortopedia",
    patients: 5,
    maxPatients: 6,
    nextSlot: "11:00",
    status: "Ocupado",
  },
  {
    name: "Dr. Ricardo Souza",
    specialty: "Neurologia",
    patients: 3,
    maxPatients: 8,
    nextSlot: "14:00",
    status: "Intervalo",
  },
]

const statusStyles = {
  Disponivel: {
    dot: "bg-[hsl(160,60%,42%)]",
    text: "text-[hsl(160,60%,42%)]",
    bg: "bg-[hsl(160,60%,42%)]/10",
  },
  Ocupado: {
    dot: "bg-[hsl(199,89%,40%)]",
    text: "text-[hsl(199,89%,40%)]",
    bg: "bg-[hsl(199,89%,40%)]/10",
  },
  Intervalo: {
    dot: "bg-[hsl(35,92%,52%)]",
    text: "text-[hsl(35,92%,52%)]",
    bg: "bg-[hsl(35,92%,52%)]/10",
  },
}

export function ActiveOffices() {
  return (
    <div className="overflow-hidden rounded-xl border border-border/60 bg-card">
      <div className="flex items-center justify-between border-b border-border/40 px-6 py-4">
        <div>
          <h3 className="text-sm font-bold text-card-foreground">Consultorios Ativos</h3>
          <p className="mt-0.5 text-[11px] text-muted-foreground">Status em tempo real</p>
        </div>
        <div className="flex items-center gap-3">
          {(["Disponivel", "Ocupado", "Intervalo"] as const).map((s) => (
            <div key={s} className="flex items-center gap-1.5">
              <span className={`h-2 w-2 rounded-full ${statusStyles[s].dot}`} />
              <span className="text-[10px] text-muted-foreground">{s}</span>
            </div>
          ))}
        </div>
      </div>
      <div className="grid grid-cols-1 gap-px bg-border/30 sm:grid-cols-2">
        {offices.map((office) => {
          const style = statusStyles[office.status]
          const occupancy = Math.round((office.patients / office.maxPatients) * 100)
          return (
            <div
              key={office.name}
              className="bg-card p-5 transition-colors hover:bg-muted/20"
            >
              <div className="flex items-start justify-between">
                <div className="flex items-center gap-3">
                  <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                    <Stethoscope className="h-5 w-5 text-primary" />
                  </div>
                  <div>
                    <h4 className="text-sm font-semibold text-card-foreground">{office.name}</h4>
                    <p className="text-[11px] text-muted-foreground">{office.specialty}</p>
                  </div>
                </div>
                <span
                  className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ${style.bg} ${style.text}`}
                >
                  <span className={`h-1.5 w-1.5 rounded-full ${style.dot}`} />
                  {office.status}
                </span>
              </div>

              {/* Occupancy bar */}
              <div className="mt-4">
                <div className="flex items-center justify-between">
                  <span className="text-[11px] text-muted-foreground">Ocupacao</span>
                  <span className="text-[11px] font-bold text-card-foreground">{occupancy}%</span>
                </div>
                <div className="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                  <div
                    className="h-full rounded-full bg-primary transition-all duration-500"
                    style={{ width: `${occupancy}%` }}
                  />
                </div>
              </div>

              <div className="mt-3 flex items-center gap-4 border-t border-border/30 pt-3">
                <div className="flex items-center gap-1.5">
                  <Users className="h-3.5 w-3.5 text-muted-foreground" />
                  <span className="text-[11px] text-muted-foreground">
                    <span className="font-bold text-card-foreground">{office.patients}</span>/{office.maxPatients} pacientes
                  </span>
                </div>
                <div className="flex items-center gap-1.5">
                  <Clock className="h-3.5 w-3.5 text-muted-foreground" />
                  <span className="text-[11px] text-muted-foreground">
                    Prox: <span className="font-bold text-card-foreground">{office.nextSlot}</span>
                  </span>
                </div>
              </div>
            </div>
          )
        })}
      </div>
    </div>
  )
}
