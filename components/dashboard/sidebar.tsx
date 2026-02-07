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
      className={`fixed left-0 top-16 bottom-0 z-40 border-r border-sidebar-border bg-sidebar-background transition-all duration-300 ease-in-out overflow-y-auto ${
        collapsed ? "w-[68px]" : "w-[252px]"
      }`}
    >
      <nav className="py-3">
        {navSections.map((section, sectionIdx) => (
          <div key={section.category} className={sectionIdx > 0 ? "mt-1" : ""}>
            {!collapsed && (
              <p className="px-5 pb-1.5 pt-4 text-[10px] font-bold uppercase tracking-[0.08em] text-sidebar-foreground/40">
                {section.category}
              </p>
            )}
            {collapsed && sectionIdx > 0 && (
              <div className="mx-4 my-2 border-t border-sidebar-border" />
            )}
            <ul>
              {section.items.map((item) => (
                <li key={item.label}>
                  {item.children ? (
                    <>
                      <button
                        onClick={() => !collapsed && toggleMenu(item.label)}
                        className={`group flex w-full items-center gap-3 px-5 py-2 text-[13px] font-medium transition-all duration-150 hover:bg-sidebar-accent/60 hover:text-sidebar-accent-foreground ${
                          collapsed ? "justify-center px-0" : ""
                        } ${openMenus[item.label] ? "text-sidebar-primary" : "text-sidebar-foreground"}`}
                        title={item.label}
                      >
                        <item.icon className={`h-[17px] w-[17px] shrink-0 transition-colors ${openMenus[item.label] ? "text-sidebar-primary" : "text-sidebar-foreground/60 group-hover:text-sidebar-foreground"}`} />
                        {!collapsed && (
                          <>
                            <span className="flex-1 text-left">{item.label}</span>
                            <ChevronDown
                              className={`h-3.5 w-3.5 text-sidebar-foreground/40 transition-transform duration-200 ${
                                openMenus[item.label] ? "rotate-180" : ""
                              }`}
                            />
                          </>
                        )}
                      </button>
                      {!collapsed && openMenus[item.label] && (
                        <ul className="overflow-hidden">
                          {item.children.map((child) => (
                            <li key={child.label}>
                              <a
                                href={child.href}
                                className="relative block py-1.5 pl-12 pr-5 text-[12px] text-sidebar-foreground/60 transition-all duration-150 hover:text-sidebar-primary before:absolute before:left-[30px] before:top-1/2 before:h-1 before:w-1 before:-translate-y-1/2 before:rounded-full before:bg-sidebar-foreground/20 hover:before:bg-sidebar-primary"
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
                      className={`group flex items-center gap-3 px-5 py-2 text-[13px] font-medium transition-all duration-150 hover:bg-sidebar-accent/60 hover:text-sidebar-accent-foreground ${
                        collapsed ? "justify-center px-0" : ""
                      } ${
                        item.active
                          ? "bg-sidebar-accent text-sidebar-primary"
                          : "text-sidebar-foreground"
                      }`}
                      title={item.label}
                    >
                      {item.active && !collapsed && (
                        <span className="absolute left-0 h-7 w-[3px] rounded-r-full bg-sidebar-primary" />
                      )}
                      <item.icon className={`h-[17px] w-[17px] shrink-0 transition-colors ${item.active ? "text-sidebar-primary" : "text-sidebar-foreground/60 group-hover:text-sidebar-foreground"}`} />
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
