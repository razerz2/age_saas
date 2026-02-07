import { DashboardLayout } from "@/components/dashboard/dashboard-layout"
import { StatCards } from "@/components/dashboard/stat-cards"
import { QuickActions } from "@/components/dashboard/quick-actions"
import { AppointmentsChart, RevenueChart } from "@/components/dashboard/charts"
import { AppointmentsTable } from "@/components/dashboard/appointments-table"
import { ActiveOffices } from "@/components/dashboard/active-offices"
import { ActivityFeed } from "@/components/dashboard/activity-feed"
import { CalendarDays } from "lucide-react"

export default function DashboardPage() {
  const today = new Date()
  const formattedDate = today.toLocaleDateString("pt-BR", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  })

  return (
    <DashboardLayout>
      {/* Page Header */}
      <div className="mb-6 flex items-end justify-between">
        <div>
          <h1 className="text-2xl font-extrabold tracking-tight text-foreground">
            Bom dia, Admin
          </h1>
          <p className="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
            <CalendarDays className="h-3.5 w-3.5" />
            {formattedDate}
          </p>
        </div>
        <div className="flex items-center gap-2">
          <button className="rounded-lg border border-border bg-card px-3.5 py-2 text-[12px] font-semibold text-card-foreground transition-colors hover:bg-muted">
            Exportar Relatorio
          </button>
          <button className="rounded-lg bg-primary px-3.5 py-2 text-[12px] font-semibold text-primary-foreground transition-colors hover:bg-primary/90">
            Novo Agendamento
          </button>
        </div>
      </div>

      {/* Quick Actions */}
      <QuickActions />

      {/* Stat Cards */}
      <div className="mt-5">
        <StatCards />
      </div>

      {/* Charts Row */}
      <div className="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <AppointmentsChart />
        <RevenueChart />
      </div>

      {/* Bottom section: Table + Activity Feed */}
      <div className="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div className="xl:col-span-2">
          <AppointmentsTable />
        </div>
        <div>
          <ActivityFeed />
        </div>
      </div>

      {/* Active Offices */}
      <div className="mt-5">
        <ActiveOffices />
      </div>
    </DashboardLayout>
  )
}
