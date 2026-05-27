export type Role = 'admin' | 'manager' | 'sales' | 'viewer';
export type ProcessStep = 1 | 2 | 3;
export type Result = 'success' | 'failure' | 'ongoing';
export type OpportunityStatus = 'active' | 'won' | 'lost' | 'hold';
export type FollowupStatus = 'pending' | 'completed' | 'cancelled';

export interface User {
  id: number;
  name: string;
  email: string;
  role: Role;
  department?: Department;
  is_active: boolean;
  last_login_at?: string;
}

export interface Department {
  id: number;
  name: string;
  code: string;
  sort_order: number;
  is_active: boolean;
}

export interface Area {
  id: number;
  name: string;
  code: string;
}

export interface OpportunityType {
  id: number;
  name: string;
  code: string;
}

export interface Client {
  id: number;
  name: string;
  contact_name?: string;
}

export interface Opportunity {
  id: number;
  title: string;
  client_name?: string;
  area?: string;
  type?: string;
  estimated_amount?: number;
  confirmed_amount?: number;
  current_process: ProcessStep;
  status: OpportunityStatus;
}

export interface AarRecord {
  id: number;
  process_step: ProcessStep;
  process_label: string;
  activity_date: string;
  result: Result | null;
  result_label: string;
  is_draft: boolean;
  q1_goal?: string;
  q2_result?: string;
  q3_cause?: string;
  q4_action?: string;
  opportunity: Opportunity;
  user?: { id: number; name: string; dept?: string };
  followup?: { description: string; scheduled_date: string; status: FollowupStatus } | null;
}

export interface ProcessStat {
  step: ProcessStep;
  label: string;
  total: number;
  success: number;
  rate: number;
}

export interface KpiStats {
  total: number;
  success: number;
  failure: number;
  rate: number;
}

export interface PaginatedData<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  links: { url: string | null; label: string; active: boolean }[];
}

export interface PageProps extends Record<string, unknown> {
  auth: { user: User };
  flash?: { success?: string; error?: string };
}
