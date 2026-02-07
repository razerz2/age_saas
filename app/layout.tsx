import type { Metadata, Viewport } from "next"
import { Inter } from "next/font/google"
import "./globals.css"

const inter = Inter({ subsets: ["latin"] })

export const metadata: Metadata = {
  title: "AllSync - Dashboard | Gestao Clinica",
  description: "Painel administrativo AllSync - Sistema completo de gestao para clinicas e consultorios medicos",
}

export const viewport: Viewport = {
  themeColor: "#0c7bb3",
  width: "device-width",
  initialScale: 1,
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="pt-BR">
      <body className={`${inter.className} font-sans`}>
        {children}
      </body>
    </html>
  )
}
