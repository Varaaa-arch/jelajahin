export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    [key: string]: unknown;
};

declare module '@inertiajs/core' {
    interface PageProps extends Record<string, unknown> {
        auth: {
            user: User;
        };
    }
}