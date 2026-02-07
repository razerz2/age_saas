"use client"

import { useState } from "react"
import { Navbar } from "./navbar"
import { Sidebar } from "./sidebar"

export function DashboardLayout({ children }: { children: React.ReactNode }) {
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false)

  return (
    <div className="min-h-screen bg-background">
      <Navbar onToggleSidebar={() => setSidebarCollapsed(!sidebarCollapsed)} />
      <Sidebar collapsed={sidebarCollapsed} />
      <main
        className={`pt-[60px] transition-all duration-300 ${
          sidebarCollapsed ? "pl-[70px]" : "pl-[260px]"
        }`}
      >
        <div className="p-6">{children}</div>
      </main>
    </div>
  )
}
