import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { MapPin, Clock, Users } from "lucide-react"

interface Office {
  name: string
  specialty: string
  patients: number
  nextSlot: string
  status: "Disponivel" | "Ocupado" | "Intervalo"
}

const offices: Office[] = [
  {
    name: "Dr. Joao Pereira",
    specialty: "Cardiologia",
    patients: 6,
    nextSlot: "10:30",
    status: "Ocupado",
  },
  {
    name: "Dra. Ana Costa",
    specialty: "Dermatologia",
    patients: 4,
    nextSlot: "09:00",
    status: "Disponivel",
  },
  {
    name: "Dra. Mariana Lima",
    specialty: "Ortopedia",
    patients: 5,
    nextSlot: "11:00",
    status: "Ocupado",
  },
  {
    name: "Dr. Ricardo Souza",
    specialty: "Neurologia",
    patients: 3,
    nextSlot: "14:00",
    status: "Intervalo",
  },
]

const statusStyles = {
  Disponivel: "bg-emerald-100 text-emerald-700",
  Ocupado: "bg-blue-100 text-blue-700",
  Intervalo: "bg-amber-100 text-amber-700",
}

export function ActiveOffices() {
  return (
    <Card className="border-0 shadow-sm">
      <CardHeader className="pb-2">
        <CardTitle className="text-lg font-bold text-foreground">
          Consultorios Ativos
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
          {offices.map((office) => (
            <div
              key={office.name}
              className="rounded-lg border border-border/60 bg-card p-4 transition-all hover:-translate-y-0.5 hover:shadow-md"
            >
              <div className="flex items-start justify-between">
                <div className="flex items-center gap-3">
                  <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground shadow-sm">
                    <MapPin className="h-5 w-5" />
                  </div>
                  <div>
                    <h6 className="text-sm font-bold text-foreground">{office.name}</h6>
                    <p className="text-xs text-muted-foreground">{office.specialty}</p>
                  </div>
                </div>
                <span
                  className={`rounded-full px-2 py-0.5 text-[10px] font-semibold ${statusStyles[office.status]}`}
                >
                  {office.status}
                </span>
              </div>
              <div className="mt-3 flex items-center gap-4 border-t border-border/40 pt-3">
                <div className="flex items-center gap-1.5">
                  <Users className="h-3.5 w-3.5 text-muted-foreground" />
                  <span className="text-xs text-muted-foreground">
                    <span className="font-bold text-primary">{office.patients}</span> pacientes
                  </span>
                </div>
                <div className="flex items-center gap-1.5">
                  <Clock className="h-3.5 w-3.5 text-muted-foreground" />
                  <span className="text-xs text-muted-foreground">
                    Prox: <span className="font-bold text-foreground">{office.nextSlot}</span>
                  </span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  )
}
