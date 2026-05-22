'use client';

import { useState, useEffect, type ReactNode } from 'react';

/**
 * ClientOnly wrapper component.
 * Renders children only after the component has mounted on the client.
 * This completely prevents React hydration mismatches (Error #418)
 * by ensuring no SSR HTML is generated for the children.
 */
export default function ClientOnly({ 
  children, 
  fallback = null 
}: { 
  children: ReactNode; 
  fallback?: ReactNode;
}) {
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  if (!mounted) {
    return <>{fallback}</>;
  }

  return <>{children}</>;
}
