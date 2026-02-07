import { DashboardLayout } from "@/components/dashboard/dashboard-layout"
import { StatCards } from "@/components/dashboard/stat-cards"
import { AppointmentsChart, RevenueChart } from "@/components/dashboard/charts"
import { AppointmentsTable } from "@/components/dashboard/appointments-table"
import { ActiveOffices } from "@/components/dashboard/active-offices"

export default function DashboardPage() {
  return (
    <DashboardLayout>
      {/* Page Header */}
      <div className="mb-6">
        <h1 className="text-2xl font-bold tracking-tight text-foreground">Dashboard</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Visao geral da clinica - dados de exemplo para preview
        </p>
      </div>

      {/* Stat Cards */}
      <StatCards />

      {/* Charts Row */}
      <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <AppointmentsChart />
        <RevenueChart />
      </div>

      {/* Appointments Table */}
      <div className="mt-6">
        <AppointmentsTable />
      </div>

      {/* Active Offices */}
      <div className="mt-6">
        <ActiveOffices />
      </div>
    </DashboardLayout>
  )
}
