import { NextResponse } from 'next/server';
import { projectsData } from '@/data/seed';

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url);
  const category = searchParams.get('category');
  const featured = searchParams.get('featured');

  let filteredProjects = [...projectsData];

  if (category && (category === 'architecture' || category === 'furniture')) {
    filteredProjects = filteredProjects.filter(p => p.category === category);
  }

  if (featured === 'true') {
    filteredProjects = filteredProjects.filter(p => p.featured);
  }

  return NextResponse.json(filteredProjects);
}
