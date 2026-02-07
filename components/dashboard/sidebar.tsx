"use client"

import { useState } from "react"
import {
  LayoutDashboard,
  CalendarCheck,
  CalendarClock,
  CalendarRange,
  Video,
  Heart,
  Users,
  Stethoscope,
  Activity,
  UserCog,
  Calendar,
  Clock,
  ClipboardList,
  FileText,
  FileCheck,
  DollarSign,
  Settings,
  Puzzle,
  BarChart3,
  ChevronDown,
  type LucideIcon,
} from "lucide-react"

interface NavItem {
  label: string
  icon: LucideIcon
  href?: string
  active?: boolean
  children?: { label: string; href: string; active?: boolean }[]
}

interface NavSection {
  category: string
  items: NavItem[]
}

const navSections: NavSection[] = [
  {
    category: "Menu Principal",
    items: [
      { label: "Dashboard", icon: LayoutDashboard, href: "/", active: true },
      { label: "Agenda", icon: CalendarCheck, href: "#" },
      { label: "Agendamentos", icon: CalendarClock, href: "#" },
      { label: "Agend. Recorrentes", icon: CalendarRange, href: "#" },
      { label: "Consultas Online", icon: Video, href: "#" },
      { label: "Atendimento", icon: Heart, href: "#" },
    ],
  },
  {
    category: "Cadastros",
    items: [
      {
        label: "Pacientes",
        icon: Users,
        children: [
          { label: "Todos os Pacientes", href: "#" },
          { label: "Novo Paciente", href: "#" },
        ],
      },
      {
        label: "Profissionais",
        icon: Stethoscope,
        children: [
          { label: "Todos os Profissionais", href: "#" },
          { label: "Novo Profissional", href: "#" },
        ],
      },
      {
        label: "Especialidades",
        icon: Activity,
        children: [
          { label: "Todas as Especialidades", href: "#" },
          { label: "Nova Especialidade", href: "#" },
        ],
      },
      {
        label: "Usuarios",
        icon: UserCog,
        children: [
          { label: "Todos os Usuarios", href: "#" },
          { label: "Novo Usuario", href: "#" },
        ],
      },
    ],
  },
  {
    category: "Config. de Calendarios",
    items: [
      {
        label: "Calendarios",
        icon: Calendar,
        children: [
          { label: "Todos os Calendarios", href: "#" },
          { label: "Novo Calendario", href: "#" },
        ],
      },
      {
        label: "Horarios",
        icon: Clock,
        children: [
          { label: "Todos os Horarios", href: "#" },
          { label: "Novo Horario", href: "#" },
        ],
      },
      {
        label: "Tipos",
        icon: ClipboardList,
        children: [
          { label: "Todos os Tipos", href: "#" },
          { label: "Novo Tipo", href: "#" },
        ],
      },
    ],
  },
  {
    category: "Formularios",
    items: [
      {
        label: "Formularios",
        icon: FileText,
        children: [
          { label: "Todos os Formularios", href: "#" },
          { label: "Novo Formulario", href: "#" },
        ],
      },
      {
        label: "Respostas",
        icon: FileCheck,
        children: [{ label: "Todas as Respostas", href: "#" }],
      },
    ],
  },
  {
    category: "Financeiro",
    items: [
      {
        label: "Financeiro",
        icon: DollarSign,
        children: [
          { label: "Dashboard", href: "#" },
          { label: "Contas", href: "#" },
          { label: "Categorias", href: "#" },
          { label: "Transacoes", href: "#" },
          { label: "Cobrancas", href: "#" },
          { label: "Comissoes", href: "#" },
          { label: "Relatorios", href: "#" },
        ],
      },
    ],
  },
  {
    category: "Sistema",
    items: [
      { label: "Configuracoes", icon: Settings, href: "#" },
    ],
  },
  {
    category: "Integracoes",
    items: [
      {
        label: "Integracoes",
        icon: Puzzle,
        children: [
          { label: "Google Calendar", href: "#" },
          { label: "Apple Calendar", href: "#" },
        ],
      },
    ],
  },
  {
    category: "Relatorios",
    items: [
      {
        label: "Relatorios",
        icon: BarChart3,
        children: [
          { label: "Agendamentos", href: "#" },
          { label: "Pacientes", href: "#" },
          { label: "Medicos", href: "#" },
          { label: "Recorrencias", href: "#" },
          { label: "Formularios", href: "#" },
          { label: "Portal do Paciente", href: "#" },
          { label: "Notificacoes", href: "#" },
        ],
      },
    ],
  },
]

export function Sidebar({ collapsed }: { collapsed: boolean }) {
  const [openMenus, setOpenMenus] = useState<Record<string, boolean>>({})

  const toggleMenu = (label: string) => {
    setOpenMenus((prev) => ({ ...prev, [label]: !prev[label] }))
  }

  return (
    <aside
      className={`fixed left-0 top-[60px] bottom-0 z-40 bg-sidebar-background text-sidebar-foreground transition-all duration-300 overflow-y-auto ${
        collapsed ? "w-[70px]" : "w-[260px]"
      }`}
    >
      <nav className="py-4">
        {navSections.map((section) => (
          <div key={section.category} className="mb-2">
            {!collapsed && (
              <p className="px-6 py-2 text-[11px] font-semibold uppercase tracking-wider text-sidebar-foreground/50">
                {section.category}
              </p>
            )}
            <ul>
              {section.items.map((item) => (
                <li key={item.label}>
                  {item.children ? (
                    <>
                      <button
                        onClick={() => !collapsed && toggleMenu(item.label)}
                        className={`flex w-full items-center gap-3 px-6 py-2.5 text-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground ${
                          collapsed ? "justify-center px-0" : ""
                        }`}
                        title={item.label}
                      >
                        <item.icon className="h-[18px] w-[18px] shrink-0" />
                        {!collapsed && (
                          <>
                            <span className="flex-1 text-left">{item.label}</span>
                            <ChevronDown
                              className={`h-4 w-4 transition-transform ${
                                openMenus[item.label] ? "rotate-180" : ""
                              }`}
                            />
                          </>
                        )}
                      </button>
                      {!collapsed && openMenus[item.label] && (
                        <ul className="bg-sidebar-accent/30">
                          {item.children.map((child) => (
                            <li key={child.label}>
                              <a
                                href={child.href}
                                className="block px-6 py-2 pl-14 text-[13px] transition-colors hover:text-sidebar-primary"
                              >
                                {child.label}
                              </a>
                            </li>
                          ))}
                        </ul>
                      )}
                    </>
                  ) : (
                    <a
                      href={item.href}
                      className={`flex items-center gap-3 px-6 py-2.5 text-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground ${
                        collapsed ? "justify-center px-0" : ""
                      } ${
                        item.active
                          ? "bg-sidebar-accent text-sidebar-primary font-medium"
                          : ""
                      }`}
                      title={item.label}
                    >
                      <item.icon className="h-[18px] w-[18px] shrink-0" />
                      {!collapsed && <span>{item.label}</span>}
                    </a>
                  )}
                </li>
              ))}
            </ul>
          </div>
        ))}
      </nav>
    </aside>
  )
}
