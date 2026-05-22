import { NextResponse } from 'next/server';
import { servicesData } from '@/data/seed';

export async function GET() {
  return NextResponse.json(servicesData);
}
