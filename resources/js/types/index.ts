export * from './flight';
export * from './booking';
export * from './payment';

export interface User {
  id: string;
  email: string;
  email_verified_at?: string;
  name: string;
  first_name: string;
  last_name: string;
  phone_number?: string;
  role: 'customer' | 'admin';
}

export interface ApiResponse<T> {
  data?: T;
  message?: string;
  errors?: Record<string, string[]>;
}

export type PageProps<
  T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
  auth: {
    user: User;
  };
  [key: string]: unknown;
};
