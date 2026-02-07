"use client"

import { Menu, Bell, HelpCircle, User, LogOut, Settings, ChevronDown, Search, X } from "lucide-react"
import { useState, useRef, useEffect } from "react"

export function Navbar({ onToggleSidebar }: { onToggleSidebar: () => void }) {
  const [profileOpen, setProfileOpen] = useState(false)
  const [notifOpen, setNotifOpen] = useState(false)
  const [searchOpen, setSearchOpen] = useState(false)
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
    <header className="fixed top-0 left-0 right-0 z-50 flex h-16 items-center border-b border-border bg-card">
      {/* Logo area */}
      <div className="flex h-full w-[252px] shrink-0 items-center border-r border-sidebar-border bg-sidebar-background px-5">
        <div className="flex items-center gap-2.5">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-sidebar-primary">
            <span className="text-sm font-black tracking-tight text-sidebar-primary-foreground">A</span>
          </div>
          <div className="flex flex-col">
            <span className="text-sm font-bold tracking-tight text-sidebar-primary-foreground">AllSync</span>
            <span className="text-[10px] text-sidebar-foreground/50">Gestao Clinica</span>
          </div>
        </div>
      </div>

      {/* Navbar content */}
      <div className="flex flex-1 items-center justify-between px-5">
        <div className="flex items-center gap-3">
          <button
            onClick={onToggleSidebar}
            className="flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
            aria-label="Toggle menu"
          >
            <Menu className="h-[18px] w-[18px]" />
          </button>

          {/* Search */}
          <div className="relative hidden md:block">
            {searchOpen ? (
              <div className="flex items-center gap-2 rounded-lg border border-border bg-muted/50 px-3 py-1.5">
                <Search className="h-4 w-4 text-muted-foreground" />
                <input
                  type="text"
                  placeholder="Buscar pacientes, profissionais..."
                  className="w-64 bg-transparent text-sm text-foreground placeholder:text-muted-foreground focus:outline-none"
                  autoFocus
                />
                <button onClick={() => setSearchOpen(false)} className="text-muted-foreground hover:text-foreground">
                  <X className="h-3.5 w-3.5" />
                </button>
              </div>
            ) : (
              <button
                onClick={() => setSearchOpen(true)}
                className="flex items-center gap-2 rounded-lg border border-border bg-muted/40 px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:bg-muted"
              >
                <Search className="h-4 w-4" />
                <span>Buscar...</span>
                <kbd className="ml-8 rounded border border-border bg-card px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">
                  {"Ctrl+K"}
                </kbd>
              </button>
            )}
          </div>
        </div>

        <div className="flex items-center gap-1">
          {/* Help */}
          <a
            href="#"
            className="flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
            title="Ajuda / Manual do Sistema"
          >
            <HelpCircle className="h-[18px] w-[18px]" />
          </a>

          {/* Notifications */}
          <div ref={notifRef} className="relative">
            <button
              onClick={() => setNotifOpen(!notifOpen)}
              className="relative flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
              aria-label="Notificacoes"
            >
              <Bell className="h-[18px] w-[18px]" />
              <span className="absolute right-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-destructive text-[9px] font-bold text-destructive-foreground ring-2 ring-card">
                3
              </span>
            </button>
            {notifOpen && (
              <div className="absolute right-0 top-full mt-2 w-80 overflow-hidden rounded-xl border border-border bg-card shadow-xl">
                <div className="flex items-center justify-between border-b border-border px-4 py-3">
                  <p className="text-sm font-semibold text-foreground">Notificacoes</p>
                  <span className="rounded-full bg-destructive/10 px-2 py-0.5 text-[10px] font-bold text-destructive">3 novas</span>
                </div>
                <div className="max-h-72 overflow-y-auto">
                  {[
                    { text: "Novo agendamento confirmado", time: "2 min atras", unread: true },
                    { text: "Paciente Maria Silva reagendou", time: "15 min atras", unread: true },
                    { text: "Fatura #1042 paga", time: "1h atras", unread: true },
                    { text: "Relatorio mensal disponivel", time: "3h atras", unread: false },
                  ].map((n, i) => (
                    <div key={i} className={`flex items-start gap-3 border-b border-border/50 px-4 py-3 last:border-0 transition-colors hover:bg-muted/50 ${n.unread ? "bg-primary/[0.03]" : ""}`}>
                      {n.unread && <span className="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary" />}
                      {!n.unread && <span className="mt-1.5 h-2 w-2 shrink-0" />}
                      <div>
                        <p className={`text-sm ${n.unread ? "font-medium text-foreground" : "text-muted-foreground"}`}>{n.text}</p>
                        <p className="mt-0.5 text-[11px] text-muted-foreground">{n.time}</p>
                      </div>
                    </div>
                  ))}
                </div>
                <div className="border-t border-border px-4 py-2.5 text-center">
                  <a href="#" className="text-xs font-semibold text-primary hover:underline">
                    Ver todas as notificacoes
                  </a>
                </div>
              </div>
            )}
          </div>

          {/* Divider */}
          <div className="mx-2 h-6 w-px bg-border" />

          {/* Profile */}
          <div ref={profileRef} className="relative">
            <button
              onClick={() => setProfileOpen(!profileOpen)}
              className="flex items-center gap-2.5 rounded-lg px-2 py-1.5 transition-colors hover:bg-muted"
            >
              <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground ring-2 ring-primary/20">
                AD
              </div>
              <div className="hidden flex-col items-start md:flex">
                <span className="text-sm font-semibold text-foreground leading-tight">Admin</span>
                <span className="text-[10px] text-muted-foreground leading-tight">Administrador</span>
              </div>
              <ChevronDown className="h-3.5 w-3.5 text-muted-foreground" />
            </button>
            {profileOpen && (
              <div className="absolute right-0 top-full mt-2 w-52 overflow-hidden rounded-xl border border-border bg-card py-1.5 shadow-xl">
                <div className="border-b border-border px-4 py-2.5">
                  <p className="text-sm font-semibold text-foreground">Admin User</p>
                  <p className="text-[11px] text-muted-foreground">admin@clinica.com</p>
                </div>
                <div className="py-1">
                  <a href="#" className="flex items-center gap-2.5 px-4 py-2 text-sm text-foreground transition-colors hover:bg-muted">
                    <User className="h-4 w-4 text-muted-foreground" />
                    Meu Perfil
                  </a>
                  <a href="#" className="flex items-center gap-2.5 px-4 py-2 text-sm text-foreground transition-colors hover:bg-muted">
                    <Settings className="h-4 w-4 text-muted-foreground" />
                    Configuracoes
                  </a>
                </div>
                <div className="border-t border-border pt-1">
                  <a href="#" className="flex items-center gap-2.5 px-4 py-2 text-sm text-destructive transition-colors hover:bg-destructive/5">
                    <LogOut className="h-4 w-4" />
                    Sair
                  </a>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </header>
  )
}
