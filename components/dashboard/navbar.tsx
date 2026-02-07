"use client"

import { Menu, Bell, HelpCircle, User, LogOut, Settings, ChevronDown } from "lucide-react"
import { useState, useRef, useEffect } from "react"

export function Navbar({ onToggleSidebar }: { onToggleSidebar: () => void }) {
  const [profileOpen, setProfileOpen] = useState(false)
  const [notifOpen, setNotifOpen] = useState(false)
  const profileRef = useRef<HTMLDivElement>(null)
  const notifRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (profileRef.current && !profileRef.current.contains(event.target as Node)) {
        setProfileOpen(false)
      }
      if (notifRef.current && !notifRef.current.contains(event.target as Node)) {
        setNotifOpen(false)
      }
    }
    document.addEventListener("mousedown", handleClickOutside)
    return () => document.removeEventListener("mousedown", handleClickOutside)
  }, [])

  return (
    <header className="fixed top-0 left-0 right-0 z-50 flex h-[60px] items-center border-b border-border bg-card shadow-sm">
      {/* Logo area */}
      <div className="flex h-full w-[260px] items-center justify-center border-r border-border bg-sidebar-background px-4">
        <span className="text-lg font-bold tracking-tight text-sidebar-primary-foreground">
          AllSync
        </span>
      </div>

      {/* Navbar content */}
      <div className="flex flex-1 items-center justify-between px-4">
        <button
          onClick={onToggleSidebar}
          className="flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
          aria-label="Toggle menu"
        >
          <Menu className="h-5 w-5" />
        </button>

        <div className="flex items-center gap-2">
          {/* Help */}
          <a
            href="#"
            className="flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
            title="Ajuda / Manual do Sistema"
          >
            <HelpCircle className="h-5 w-5" />
          </a>

          {/* Notifications */}
          <div ref={notifRef} className="relative">
            <button
              onClick={() => setNotifOpen(!notifOpen)}
              className="relative flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
              aria-label="Notificacoes"
            >
              <Bell className="h-5 w-5" />
              <span className="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-destructive text-[10px] font-bold text-destructive-foreground">
                3
              </span>
            </button>
            {notifOpen && (
              <div className="absolute right-0 top-full mt-2 w-80 rounded-lg border border-border bg-card p-0 shadow-lg">
                <div className="border-b border-border px-4 py-3">
                  <p className="text-sm font-semibold text-foreground">Notificacoes</p>
                </div>
                <div className="max-h-64 overflow-y-auto">
                  {[
                    { text: "Novo agendamento confirmado", time: "2 min atras" },
                    { text: "Paciente Maria Silva reagendou", time: "15 min atras" },
                    { text: "Fatura #1042 paga", time: "1h atras" },
                  ].map((n, i) => (
                    <div key={i} className="border-b border-border px-4 py-3 last:border-0 hover:bg-muted/50">
                      <p className="text-sm text-foreground">{n.text}</p>
                      <p className="mt-0.5 text-xs text-muted-foreground">{n.time}</p>
                    </div>
                  ))}
                </div>
                <div className="border-t border-border px-4 py-2">
                  <a href="#" className="text-xs font-medium text-primary hover:underline">
                    Ver todas as notificacoes
                  </a>
                </div>
              </div>
            )}
          </div>

          {/* Profile */}
          <div ref={profileRef} className="relative">
            <button
              onClick={() => setProfileOpen(!profileOpen)}
              className="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-muted"
            >
              <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground">
                AD
              </div>
              <span className="hidden text-foreground md:inline">Admin</span>
              <ChevronDown className="h-4 w-4 text-muted-foreground" />
            </button>
            {profileOpen && (
              <div className="absolute right-0 top-full mt-2 w-48 rounded-lg border border-border bg-card py-1 shadow-lg">
                <a href="#" className="flex items-center gap-2 px-4 py-2 text-sm text-foreground hover:bg-muted">
                  <User className="h-4 w-4" />
                  Meu Perfil
                </a>
                <a href="#" className="flex items-center gap-2 px-4 py-2 text-sm text-foreground hover:bg-muted">
                  <Settings className="h-4 w-4" />
                  Configuracoes
                </a>
                <div className="my-1 border-t border-border" />
                <a href="#" className="flex items-center gap-2 px-4 py-2 text-sm text-destructive hover:bg-muted">
                  <LogOut className="h-4 w-4" />
                  Sair
                </a>
              </div>
            )}
          </div>
        </div>
      </div>
    </header>
  )
}
