export type Source = {
    id: number;
    name: string;
    feed_url: string;
    site_url: string;
    is_active: boolean;
    last_success_at: string | null;
    last_error_at: string | null;
    last_error_message: string | null;
    created_at: string;
    updated_at: string;
    is_subscribed: boolean;
};
