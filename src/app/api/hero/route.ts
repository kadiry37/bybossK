import { NextResponse } from 'next/server';
import { heroData } from '@/data/seed';

export async function GET() {
  return NextResponse.json(heroData);
}
