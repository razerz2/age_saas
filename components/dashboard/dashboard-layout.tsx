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
        className={`min-h-screen pt-16 transition-all duration-300 ease-in-out ${
          sidebarCollapsed ? "pl-[68px]" : "pl-[252px]"
        }`}
      >
        <div className="p-6">{children}</div>
      </main>
    </div>
  )
}
