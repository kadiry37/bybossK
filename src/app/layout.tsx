import type { Metadata } from "next";
import { Inter, Playfair_Display } from "next/font/google";
import "./globals.css";
import { Toaster } from "@/components/ui/toaster";

const inter = Inter({
  variable: "--font-inter",
  subsets: ["latin"],
  display: "swap",
});

const playfair = Playfair_Display({
  variable: "--font-playfair",
  subsets: ["latin"],
  display: "swap",
});

export const metadata: Metadata = {
  title: "ATELIER NOIR | Ultra-Premium Architecture & Furniture",
  description: "Modern estetik ve zamansız tasarımın buluştuğu noktada, mekanlarınızı sanata dönüştürüyoruz. Mimari tasarım, iç mekan ve özel mobilya alanında lüks çözümler.",
  keywords: ["mimari", "mobilya", "iç mekan", "tasarım", "lüks", "architecture", "furniture", "interior design", "Istanbul"],
  authors: [{ name: "ATELIER NOIR" }],
  icons: {
    icon: "/logo.svg",
  },
  openGraph: {
    title: "ATELIER NOIR | Where Vision Meets Architecture",
    description: "Modern estetik ve zamansız tasarımın buluştuğu noktada, mekanlarınızı sanata dönüştürüyoruz.",
    type: "website",
  },
  twitter: {
    card: "summary_large_image",
    title: "ATELIER NOIR | Ultra-Premium Architecture & Furniture",
    description: "Modern estetik ve zamansız tasarımın buluştuğu noktada, mekanlarınızı sanata dönüştürüyoruz.",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="tr" suppressHydrationWarning className="scroll-smooth dark">
      <body
        className={`${inter.variable} ${playfair.variable} font-sans antialiased bg-noir-900 text-white`}
      >
        {children}
        <Toaster />
      </body>
    </html>
  );
}
