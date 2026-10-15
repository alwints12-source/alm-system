--
-- PostgreSQL database dump
--


-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4


--
-- Name: asset_condition; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.asset_condition AS ENUM (
    'excellent',
    'good',
    'fair',
    'poor',
    'unserviceable'
);


--
-- Name: asset_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.asset_status AS ENUM (
    'active',
    'in_maintenance',
    'pending_acknowledgement',
    'in_storage',
    'disposed',
    'lost'
);


--
-- Name: assignment_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.assignment_status AS ENUM (
    'pending_acknowledgement',
    'acknowledged',
    'returned',
    'transferred'
);


--
-- Name: maintenance_type; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.maintenance_type AS ENUM (
    'preventive',
    'corrective',
    'predictive',
    'emergency'
);


--
-- Name: mediable_type; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.mediable_type AS ENUM (
    'asset',
    'work_order',
    'maintenance_history'
);


--
-- Name: notification_channel; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.notification_channel AS ENUM (
    'in_app',
    'email',
    'both'
);


--
-- Name: priority_level; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.priority_level AS ENUM (
    'critical',
    'high',
    'medium',
    'low'
);


--
-- Name: schedule_frequency; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.schedule_frequency AS ENUM (
    'daily',
    'weekly',
    'monthly',
    'quarterly',
    'semi_annual',
    'annual'
);


--
-- Name: user_role; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.user_role AS ENUM (
    'administrative_admin',
    'technical_admin',
    'asset_holder',
    'technician'
);


--
-- Name: work_order_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.work_order_status AS ENUM (
    'pending',
    'assigned',
    'in_progress',
    'on_hold',
    'completed',
    'overdue',
    'cancelled'
);


--
-- Name: set_updated_at(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$;




--
-- Name: asset_assignments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.asset_assignments (
    id bigint NOT NULL,
    asset_id bigint NOT NULL,
    holder_id bigint NOT NULL,
    assigned_by bigint,
    status public.assignment_status DEFAULT 'pending_acknowledgement'::public.assignment_status NOT NULL,
    assigned_at timestamp with time zone DEFAULT now() NOT NULL,
    acknowledged_at timestamp with time zone,
    returned_at timestamp with time zone,
    assignment_notes text,
    return_condition public.asset_condition,
    return_notes text,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_assignment_ack CHECK (((status <> 'acknowledged'::public.assignment_status) OR (acknowledged_at IS NOT NULL))),
    CONSTRAINT chk_assignment_dates CHECK (((returned_at IS NULL) OR (returned_at >= assigned_at))),
    CONSTRAINT chk_assignment_return CHECK (((status <> 'returned'::public.assignment_status) OR (returned_at IS NOT NULL)))
);


--
-- Name: asset_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.asset_assignments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: asset_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.asset_assignments_id_seq OWNED BY public.asset_assignments.id;


--
-- Name: asset_categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.asset_categories (
    id bigint NOT NULL,
    parent_id bigint,
    code character varying(30) NOT NULL,
    name character varying(150) NOT NULL,
    description text,
    default_useful_life_yrs integer,
    default_salvage_rate numeric(5,4) DEFAULT 0.0000,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_category_life CHECK (((default_useful_life_yrs IS NULL) OR (default_useful_life_yrs > 0))),
    CONSTRAINT chk_category_no_self_parent CHECK (((parent_id IS NULL) OR (parent_id <> id))),
    CONSTRAINT chk_category_salvage CHECK (((default_salvage_rate >= (0)::numeric) AND (default_salvage_rate < (1)::numeric)))
);


--
-- Name: asset_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.asset_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: asset_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.asset_categories_id_seq OWNED BY public.asset_categories.id;


--
-- Name: asset_disposals; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.asset_disposals (
    id bigint NOT NULL,
    asset_id bigint NOT NULL,
    reason text NOT NULL,
    requested_by bigint NOT NULL,
    requested_at timestamp(0) with time zone NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    approved_by bigint,
    approved_at timestamp(0) with time zone,
    disposal_method character varying(30),
    rejection_reason text,
    source_work_order_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: asset_disposals_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.asset_disposals_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: asset_disposals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.asset_disposals_id_seq OWNED BY public.asset_disposals.id;


--
-- Name: asset_transfers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.asset_transfers (
    id bigint NOT NULL,
    asset_id bigint NOT NULL,
    from_holder_id bigint,
    to_holder_id bigint NOT NULL,
    from_location_id bigint,
    to_location_id bigint,
    reason text,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    requested_by bigint NOT NULL,
    requested_at timestamp(0) with time zone NOT NULL,
    approved_by bigint,
    approved_at timestamp(0) with time zone,
    rejection_reason text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: asset_transfers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.asset_transfers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: asset_transfers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.asset_transfers_id_seq OWNED BY public.asset_transfers.id;


--
-- Name: assets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.assets (
    id bigint NOT NULL,
    asset_tag character varying(50) NOT NULL,
    serial_number character varying(120),
    name character varying(200) NOT NULL,
    description text,
    category_id bigint NOT NULL,
    location_id bigint,
    manufacturer character varying(150),
    model character varying(150),
    status public.asset_status DEFAULT 'active'::public.asset_status NOT NULL,
    condition public.asset_condition DEFAULT 'good'::public.asset_condition NOT NULL,
    acquisition_date date NOT NULL,
    acquisition_cost numeric(14,2) NOT NULL,
    supplier character varying(200),
    purchase_order_ref character varying(100),
    useful_life_years integer NOT NULL,
    salvage_value numeric(14,2) DEFAULT 0.00 NOT NULL,
    current_book_value numeric(14,2),
    warranty_expiry_date date,
    disposal_date date,
    disposal_reason text,
    created_by bigint,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    CONSTRAINT chk_assets_cost CHECK ((acquisition_cost >= (0)::numeric)),
    CONSTRAINT chk_assets_disposal_date CHECK (((disposal_date IS NULL) OR (disposal_date >= acquisition_date))),
    CONSTRAINT chk_assets_disposed CHECK (((status <> 'disposed'::public.asset_status) OR ((status = 'disposed'::public.asset_status) AND (disposal_date IS NOT NULL)))),
    CONSTRAINT chk_assets_life CHECK ((useful_life_years > 0)),
    CONSTRAINT chk_assets_salvage CHECK (((salvage_value >= (0)::numeric) AND (salvage_value <= acquisition_cost))),
    CONSTRAINT chk_assets_warranty CHECK (((warranty_expiry_date IS NULL) OR (warranty_expiry_date >= acquisition_date)))
);


--
-- Name: assets_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.assets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: assets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.assets_id_seq OWNED BY public.assets.id;


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    id bigint NOT NULL,
    user_id bigint,
    action character varying(50) NOT NULL,
    entity_type character varying(50) NOT NULL,
    entity_id bigint,
    old_values jsonb,
    new_values jsonb,
    ip_address inet,
    user_agent text,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.audit_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: depreciation_records; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.depreciation_records (
    id bigint NOT NULL,
    asset_id bigint NOT NULL,
    period_year integer NOT NULL,
    period_month integer NOT NULL,
    opening_book_value numeric(14,2) NOT NULL,
    depreciation_amount numeric(14,2) NOT NULL,
    accumulated_depreciation numeric(14,2) NOT NULL,
    closing_book_value numeric(14,2) NOT NULL,
    method character varying(30) DEFAULT 'straight_line'::character varying NOT NULL,
    calculated_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_depr_method CHECK (((method)::text = ANY ((ARRAY['straight_line'::character varying, 'declining_balance'::character varying, 'sum_of_years'::character varying])::text[]))),
    CONSTRAINT chk_depr_month CHECK (((period_month >= 1) AND (period_month <= 12))),
    CONSTRAINT chk_depr_values CHECK (((depreciation_amount >= (0)::numeric) AND (closing_book_value >= (0)::numeric) AND (opening_book_value >= (0)::numeric))),
    CONSTRAINT chk_depr_year CHECK (((period_year >= 1900) AND (period_year <= 2200)))
);


--
-- Name: depreciation_records_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.depreciation_records_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: depreciation_records_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.depreciation_records_id_seq OWNED BY public.depreciation_records.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: locations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.locations (
    id bigint NOT NULL,
    parent_id bigint,
    code character varying(30) NOT NULL,
    name character varying(150) NOT NULL,
    building character varying(150),
    floor character varying(50),
    room character varying(50),
    address text,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_location_no_self_parent CHECK (((parent_id IS NULL) OR (parent_id <> id)))
);


--
-- Name: locations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.locations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: locations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.locations_id_seq OWNED BY public.locations.id;


--
-- Name: maintenance_history; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.maintenance_history (
    id bigint NOT NULL,
    work_order_id bigint NOT NULL,
    asset_id bigint NOT NULL,
    technician_id bigint,
    performed_at timestamp with time zone NOT NULL,
    maintenance_type public.maintenance_type NOT NULL,
    work_performed text NOT NULL,
    parts_replaced text,
    technician_notes text,
    labour_hours numeric(8,2),
    labour_cost numeric(14,2) DEFAULT 0.00,
    parts_cost numeric(14,2) DEFAULT 0.00,
    other_cost numeric(14,2) DEFAULT 0.00,
    total_cost numeric(14,2) GENERATED ALWAYS AS (((COALESCE(labour_cost, (0)::numeric) + COALESCE(parts_cost, (0)::numeric)) + COALESCE(other_cost, (0)::numeric))) STORED,
    condition_before public.asset_condition,
    condition_after public.asset_condition,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_history_costs CHECK (((COALESCE(labour_cost, (0)::numeric) >= (0)::numeric) AND (COALESCE(parts_cost, (0)::numeric) >= (0)::numeric) AND (COALESCE(other_cost, (0)::numeric) >= (0)::numeric))),
    CONSTRAINT chk_history_hours CHECK (((labour_hours IS NULL) OR (labour_hours >= (0)::numeric)))
);


--
-- Name: maintenance_history_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.maintenance_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: maintenance_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.maintenance_history_id_seq OWNED BY public.maintenance_history.id;


--
-- Name: maintenance_schedules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.maintenance_schedules (
    id bigint NOT NULL,
    asset_id bigint NOT NULL,
    title character varying(200) NOT NULL,
    description text,
    maintenance_type public.maintenance_type DEFAULT 'preventive'::public.maintenance_type NOT NULL,
    frequency public.schedule_frequency NOT NULL,
    interval_days integer NOT NULL,
    start_date date NOT NULL,
    end_date date,
    next_due_date date NOT NULL,
    last_generated_at timestamp with time zone,
    default_assignee_id bigint,
    estimated_duration_hrs numeric(6,2),
    is_active boolean DEFAULT true NOT NULL,
    created_by bigint,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_schedule_dates CHECK (((end_date IS NULL) OR (end_date >= start_date))),
    CONSTRAINT chk_schedule_duration CHECK (((estimated_duration_hrs IS NULL) OR (estimated_duration_hrs > (0)::numeric))),
    CONSTRAINT chk_schedule_interval CHECK ((interval_days > 0))
);


--
-- Name: maintenance_schedules_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.maintenance_schedules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: maintenance_schedules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.maintenance_schedules_id_seq OWNED BY public.maintenance_schedules.id;


--
-- Name: media; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.media (
    id bigint NOT NULL,
    mediable_type public.mediable_type NOT NULL,
    mediable_id bigint NOT NULL,
    s3_bucket character varying(150) NOT NULL,
    s3_key character varying(500) NOT NULL,
    s3_region character varying(50) DEFAULT 'ap-southeast-2'::character varying NOT NULL,
    original_filename character varying(255) NOT NULL,
    mime_type character varying(100) NOT NULL,
    file_size_bytes bigint NOT NULL,
    checksum_sha256 character(64),
    width_px integer,
    height_px integer,
    is_primary boolean DEFAULT false NOT NULL,
    caption character varying(255),
    sort_order integer DEFAULT 0 NOT NULL,
    uploaded_by bigint,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    CONSTRAINT chk_media_dimensions CHECK (((((mime_type)::text = 'application/pdf'::text) AND (width_px IS NULL) AND (height_px IS NULL)) OR (((mime_type)::text <> 'application/pdf'::text) AND (width_px > 0) AND (height_px > 0)))),
    CONSTRAINT chk_media_mime CHECK (((mime_type)::text = ANY ((ARRAY['image/jpeg'::character varying, 'image/png'::character varying, 'image/webp'::character varying, 'image/heic'::character varying, 'application/pdf'::character varying])::text[]))),
    CONSTRAINT chk_media_size CHECK (((file_size_bytes > 0) AND (file_size_bytes <= 10485760))),
    CONSTRAINT chk_media_sort CHECK ((sort_order >= 0))
);


--
-- Name: media_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.media_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: media_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.media_id_seq OWNED BY public.media.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notifications (
    id bigint NOT NULL,
    recipient_id bigint NOT NULL,
    type character varying(100) NOT NULL,
    channel public.notification_channel DEFAULT 'in_app'::public.notification_channel NOT NULL,
    title character varying(200) NOT NULL,
    body text NOT NULL,
    related_type character varying(50),
    related_id bigint,
    is_read boolean DEFAULT false NOT NULL,
    read_at timestamp with time zone,
    emailed_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_notification_read CHECK ((((is_read = false) AND (read_at IS NULL)) OR ((is_read = true) AND (read_at IS NOT NULL)))),
    CONSTRAINT chk_notification_related CHECK ((((related_type IS NULL) AND (related_id IS NULL)) OR ((related_type IS NOT NULL) AND (related_id IS NOT NULL))))
);


--
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.notifications_id_seq OWNED BY public.notifications.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    token_hash character varying(255) NOT NULL,
    expires_at timestamp with time zone NOT NULL,
    used_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_reset_expiry CHECK ((expires_at > created_at))
);


--
-- Name: password_reset_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.password_reset_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: password_reset_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.password_reset_tokens_id_seq OWNED BY public.password_reset_tokens.id;


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    slug character varying(100) NOT NULL,
    resource character varying(50) NOT NULL,
    action character varying(50) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: role_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_permissions (
    id bigint NOT NULL,
    role public.user_role NOT NULL,
    permission_id bigint NOT NULL,
    granted_at timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: role_permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.role_permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: role_permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.role_permissions_id_seq OWNED BY public.role_permissions.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: sla_policies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sla_policies (
    id bigint NOT NULL,
    name character varying(150) NOT NULL,
    description text,
    maintenance_type public.maintenance_type,
    priority public.priority_level NOT NULL,
    category_id bigint,
    response_time_hours integer NOT NULL,
    resolution_time_hours integer NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_sla_resolution CHECK ((resolution_time_hours >= response_time_hours)),
    CONSTRAINT chk_sla_response CHECK ((response_time_hours > 0))
);


--
-- Name: sla_policies_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sla_policies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sla_policies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sla_policies_id_seq OWNED BY public.sla_policies.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    employee_id character varying(50) NOT NULL,
    first_name character varying(100) NOT NULL,
    last_name character varying(100) NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) CONSTRAINT users_password_hash_not_null NOT NULL,
    role public.user_role NOT NULL,
    department character varying(150),
    "position" character varying(150),
    contact_number character varying(30),
    is_active boolean DEFAULT true NOT NULL,
    mfa_enabled boolean DEFAULT false NOT NULL,
    mfa_secret character varying(255),
    mfa_confirmed_at timestamp with time zone,
    last_login_at timestamp with time zone,
    email_verified_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    CONSTRAINT chk_users_email CHECK (((email)::text ~* '^[^@\s]+@[^@\s]+\.[^@\s]+$'::text)),
    CONSTRAINT chk_users_mfa CHECK (((mfa_enabled = false) OR ((mfa_enabled = true) AND (mfa_secret IS NOT NULL))))
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: work_order_activity_log; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.work_order_activity_log (
    id bigint NOT NULL,
    work_order_id bigint NOT NULL,
    event_description character varying(255) NOT NULL,
    created_by bigint,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: work_order_activity_log_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.work_order_activity_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: work_order_activity_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.work_order_activity_log_id_seq OWNED BY public.work_order_activity_log.id;


--
-- Name: work_order_checklist_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.work_order_checklist_items (
    id bigint NOT NULL,
    work_order_id bigint NOT NULL,
    description character varying(255) NOT NULL,
    is_completed boolean DEFAULT false NOT NULL,
    completed_at timestamp(0) with time zone,
    sort_order integer DEFAULT 0 NOT NULL
);


--
-- Name: work_order_checklist_items_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.work_order_checklist_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: work_order_checklist_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.work_order_checklist_items_id_seq OWNED BY public.work_order_checklist_items.id;


--
-- Name: work_orders; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.work_orders (
    id bigint NOT NULL,
    work_order_number character varying(50) NOT NULL,
    asset_id bigint NOT NULL,
    schedule_id bigint,
    title character varying(200) NOT NULL,
    description text,
    maintenance_type public.maintenance_type NOT NULL,
    priority public.priority_level DEFAULT 'medium'::public.priority_level NOT NULL,
    status public.work_order_status DEFAULT 'pending'::public.work_order_status NOT NULL,
    requested_by bigint,
    assigned_to bigint,
    approved_by bigint,
    reported_at timestamp with time zone DEFAULT now() NOT NULL,
    due_date timestamp(0) with time zone,
    started_at timestamp with time zone,
    completed_at timestamp with time zone,
    sla_id bigint,
    sla_breached boolean DEFAULT false NOT NULL,
    sla_breached_at timestamp with time zone,
    estimated_cost numeric(14,2),
    actual_cost numeric(14,2),
    downtime_hours numeric(8,2),
    resolution_notes text,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    technician_notes text,
    approved_at timestamp(0) with time zone,
    CONSTRAINT chk_wo_assigned CHECK (((status <> ALL (ARRAY['assigned'::public.work_order_status, 'in_progress'::public.work_order_status])) OR (assigned_to IS NOT NULL))),
    CONSTRAINT chk_wo_breach CHECK (((sla_breached = false) OR ((sla_breached = true) AND (sla_breached_at IS NOT NULL)))),
    CONSTRAINT chk_wo_completed CHECK (((completed_at IS NULL) OR (completed_at >= reported_at))),
    CONSTRAINT chk_wo_completion CHECK (((status <> 'completed'::public.work_order_status) OR ((completed_at IS NOT NULL) AND (assigned_to IS NOT NULL)))),
    CONSTRAINT chk_wo_costs CHECK ((((estimated_cost IS NULL) OR (estimated_cost >= (0)::numeric)) AND ((actual_cost IS NULL) OR (actual_cost >= (0)::numeric)))),
    CONSTRAINT chk_wo_downtime CHECK (((downtime_hours IS NULL) OR (downtime_hours >= (0)::numeric))),
    CONSTRAINT chk_wo_started CHECK (((started_at IS NULL) OR (started_at >= reported_at)))
);


--
-- Name: work_orders_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.work_orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: work_orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.work_orders_id_seq OWNED BY public.work_orders.id;


--
-- Name: asset_assignments id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_assignments ALTER COLUMN id SET DEFAULT nextval('public.asset_assignments_id_seq'::regclass);


--
-- Name: asset_categories id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_categories ALTER COLUMN id SET DEFAULT nextval('public.asset_categories_id_seq'::regclass);


--
-- Name: asset_disposals id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_disposals ALTER COLUMN id SET DEFAULT nextval('public.asset_disposals_id_seq'::regclass);


--
-- Name: asset_transfers id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_transfers ALTER COLUMN id SET DEFAULT nextval('public.asset_transfers_id_seq'::regclass);


--
-- Name: assets id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.assets ALTER COLUMN id SET DEFAULT nextval('public.assets_id_seq'::regclass);


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- Name: depreciation_records id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.depreciation_records ALTER COLUMN id SET DEFAULT nextval('public.depreciation_records_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: locations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations ALTER COLUMN id SET DEFAULT nextval('public.locations_id_seq'::regclass);


--
-- Name: maintenance_history id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_history ALTER COLUMN id SET DEFAULT nextval('public.maintenance_history_id_seq'::regclass);


--
-- Name: maintenance_schedules id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_schedules ALTER COLUMN id SET DEFAULT nextval('public.maintenance_schedules_id_seq'::regclass);


--
-- Name: media id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media ALTER COLUMN id SET DEFAULT nextval('public.media_id_seq'::regclass);


--
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications ALTER COLUMN id SET DEFAULT nextval('public.notifications_id_seq'::regclass);


--
-- Name: password_reset_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens ALTER COLUMN id SET DEFAULT nextval('public.password_reset_tokens_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: role_permissions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions ALTER COLUMN id SET DEFAULT nextval('public.role_permissions_id_seq'::regclass);


--
-- Name: sla_policies id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sla_policies ALTER COLUMN id SET DEFAULT nextval('public.sla_policies_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: work_order_activity_log id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_order_activity_log ALTER COLUMN id SET DEFAULT nextval('public.work_order_activity_log_id_seq'::regclass);


--
-- Name: work_order_checklist_items id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_order_checklist_items ALTER COLUMN id SET DEFAULT nextval('public.work_order_checklist_items_id_seq'::regclass);


--
-- Name: work_orders id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_orders ALTER COLUMN id SET DEFAULT nextval('public.work_orders_id_seq'::regclass);


--
-- Name: asset_assignments asset_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_assignments
    ADD CONSTRAINT asset_assignments_pkey PRIMARY KEY (id);


--
-- Name: asset_categories asset_categories_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_categories
    ADD CONSTRAINT asset_categories_code_key UNIQUE (code);


--
-- Name: asset_categories asset_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_categories
    ADD CONSTRAINT asset_categories_pkey PRIMARY KEY (id);


--
-- Name: asset_disposals asset_disposals_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_disposals
    ADD CONSTRAINT asset_disposals_pkey PRIMARY KEY (id);


--
-- Name: asset_transfers asset_transfers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_transfers
    ADD CONSTRAINT asset_transfers_pkey PRIMARY KEY (id);


--
-- Name: assets assets_asset_tag_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_asset_tag_key UNIQUE (asset_tag);


--
-- Name: assets assets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_pkey PRIMARY KEY (id);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: depreciation_records depreciation_records_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.depreciation_records
    ADD CONSTRAINT depreciation_records_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: locations locations_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations
    ADD CONSTRAINT locations_code_key UNIQUE (code);


--
-- Name: locations locations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations
    ADD CONSTRAINT locations_pkey PRIMARY KEY (id);


--
-- Name: maintenance_history maintenance_history_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_history
    ADD CONSTRAINT maintenance_history_pkey PRIMARY KEY (id);


--
-- Name: maintenance_schedules maintenance_schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT maintenance_schedules_pkey PRIMARY KEY (id);


--
-- Name: media media_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_pkey PRIMARY KEY (id);


--
-- Name: media media_s3_key_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_s3_key_key UNIQUE (s3_key);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_slug_key UNIQUE (slug);


--
-- Name: role_permissions role_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: sla_policies sla_policies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sla_policies
    ADD CONSTRAINT sla_policies_pkey PRIMARY KEY (id);


--
-- Name: depreciation_records uq_depreciation_period; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.depreciation_records
    ADD CONSTRAINT uq_depreciation_period UNIQUE (asset_id, period_year, period_month);


--
-- Name: permissions uq_permissions_resource_action; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT uq_permissions_resource_action UNIQUE (resource, action);


--
-- Name: role_permissions uq_role_permission; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT uq_role_permission UNIQUE (role, permission_id);


--
-- Name: sla_policies uq_sla_scope; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sla_policies
    ADD CONSTRAINT uq_sla_scope UNIQUE NULLS NOT DISTINCT (priority, maintenance_type, category_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_employee_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_employee_id_key UNIQUE (employee_id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: work_order_activity_log work_order_activity_log_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_order_activity_log
    ADD CONSTRAINT work_order_activity_log_pkey PRIMARY KEY (id);


--
-- Name: work_order_checklist_items work_order_checklist_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_order_checklist_items
    ADD CONSTRAINT work_order_checklist_items_pkey PRIMARY KEY (id);


--
-- Name: work_orders work_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_orders
    ADD CONSTRAINT work_orders_pkey PRIMARY KEY (id);


--
-- Name: work_orders work_orders_work_order_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_orders
    ADD CONSTRAINT work_orders_work_order_number_key UNIQUE (work_order_number);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: failed_jobs_connection_queue_failed_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);


--
-- Name: idx_assets_category; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_assets_category ON public.assets USING btree (category_id);


--
-- Name: idx_assets_location; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_assets_location ON public.assets USING btree (location_id);


--
-- Name: idx_assets_serial; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_assets_serial ON public.assets USING btree (serial_number);


--
-- Name: idx_assets_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_assets_status ON public.assets USING btree (status) WHERE (deleted_at IS NULL);


--
-- Name: idx_assets_tag; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_assets_tag ON public.assets USING btree (asset_tag);


--
-- Name: idx_assignments_asset; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_assignments_asset ON public.asset_assignments USING btree (asset_id);


--
-- Name: idx_assignments_holder; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_assignments_holder ON public.asset_assignments USING btree (holder_id);


--
-- Name: idx_audit_created; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_created ON public.audit_logs USING btree (created_at DESC);


--
-- Name: idx_audit_entity; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_entity ON public.audit_logs USING btree (entity_type, entity_id);


--
-- Name: idx_audit_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_user ON public.audit_logs USING btree (user_id);


--
-- Name: idx_categories_parent; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_categories_parent ON public.asset_categories USING btree (parent_id);


--
-- Name: idx_depreciation_asset; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_depreciation_asset ON public.depreciation_records USING btree (asset_id);


--
-- Name: idx_depreciation_period; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_depreciation_period ON public.depreciation_records USING btree (period_year, period_month);


--
-- Name: idx_history_asset; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_history_asset ON public.maintenance_history USING btree (asset_id);


--
-- Name: idx_history_date; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_history_date ON public.maintenance_history USING btree (performed_at);


--
-- Name: idx_history_wo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_history_wo ON public.maintenance_history USING btree (work_order_id);


--
-- Name: idx_locations_parent; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_locations_parent ON public.locations USING btree (parent_id);


--
-- Name: idx_media_key; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_media_key ON public.media USING btree (s3_key);


--
-- Name: idx_media_owner; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_media_owner ON public.media USING btree (mediable_type, mediable_id) WHERE (deleted_at IS NULL);


--
-- Name: idx_notifications_created; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_notifications_created ON public.notifications USING btree (created_at DESC);


--
-- Name: idx_notifications_recipient; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_notifications_recipient ON public.notifications USING btree (recipient_id, is_read);


--
-- Name: idx_reset_token; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_reset_token ON public.password_reset_tokens USING btree (token_hash);


--
-- Name: idx_reset_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_reset_user ON public.password_reset_tokens USING btree (user_id);


--
-- Name: idx_role_permissions_role; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_role_permissions_role ON public.role_permissions USING btree (role);


--
-- Name: idx_schedules_asset; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_schedules_asset ON public.maintenance_schedules USING btree (asset_id);


--
-- Name: idx_schedules_due; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_schedules_due ON public.maintenance_schedules USING btree (next_due_date) WHERE (is_active = true);


--
-- Name: idx_users_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_active ON public.users USING btree (is_active) WHERE (deleted_at IS NULL);


--
-- Name: idx_users_email; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_email ON public.users USING btree (email);


--
-- Name: idx_users_role; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_role ON public.users USING btree (role);


--
-- Name: idx_wo_asset; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_wo_asset ON public.work_orders USING btree (asset_id);


--
-- Name: idx_wo_assignee; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_wo_assignee ON public.work_orders USING btree (assigned_to);


--
-- Name: idx_wo_due; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_wo_due ON public.work_orders USING btree (due_date) WHERE (status <> ALL (ARRAY['completed'::public.work_order_status, 'cancelled'::public.work_order_status]));


--
-- Name: idx_wo_number; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_wo_number ON public.work_orders USING btree (work_order_number);


--
-- Name: idx_wo_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_wo_status ON public.work_orders USING btree (status);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: uq_asset_active_assignment; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_asset_active_assignment ON public.asset_assignments USING btree (asset_id) WHERE (status = ANY (ARRAY['pending_acknowledgement'::public.assignment_status, 'acknowledged'::public.assignment_status]));


--
-- Name: uq_media_primary; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_media_primary ON public.media USING btree (mediable_type, mediable_id) WHERE ((is_primary = true) AND (deleted_at IS NULL));


--
-- Name: asset_assignments trg_asset_assignments_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_asset_assignments_updated_at BEFORE UPDATE ON public.asset_assignments FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: asset_categories trg_asset_categories_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_asset_categories_updated_at BEFORE UPDATE ON public.asset_categories FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: assets trg_assets_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_assets_updated_at BEFORE UPDATE ON public.assets FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: locations trg_locations_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_locations_updated_at BEFORE UPDATE ON public.locations FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: maintenance_schedules trg_maintenance_schedules_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_maintenance_schedules_updated_at BEFORE UPDATE ON public.maintenance_schedules FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: media trg_media_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_media_updated_at BEFORE UPDATE ON public.media FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: sla_policies trg_sla_policies_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_sla_policies_updated_at BEFORE UPDATE ON public.sla_policies FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: users trg_users_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_users_updated_at BEFORE UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: work_orders trg_work_orders_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_work_orders_updated_at BEFORE UPDATE ON public.work_orders FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- Name: asset_assignments asset_assignments_asset_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_assignments
    ADD CONSTRAINT asset_assignments_asset_id_fkey FOREIGN KEY (asset_id) REFERENCES public.assets(id) ON DELETE CASCADE;


--
-- Name: asset_assignments asset_assignments_assigned_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_assignments
    ADD CONSTRAINT asset_assignments_assigned_by_fkey FOREIGN KEY (assigned_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: asset_assignments asset_assignments_holder_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_assignments
    ADD CONSTRAINT asset_assignments_holder_id_fkey FOREIGN KEY (holder_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: asset_categories asset_categories_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_categories
    ADD CONSTRAINT asset_categories_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES public.asset_categories(id) ON DELETE SET NULL;


--
-- Name: asset_disposals asset_disposals_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_disposals
    ADD CONSTRAINT asset_disposals_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: asset_disposals asset_disposals_asset_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_disposals
    ADD CONSTRAINT asset_disposals_asset_id_foreign FOREIGN KEY (asset_id) REFERENCES public.assets(id) ON DELETE CASCADE;


--
-- Name: asset_disposals asset_disposals_requested_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_disposals
    ADD CONSTRAINT asset_disposals_requested_by_foreign FOREIGN KEY (requested_by) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: asset_disposals asset_disposals_source_work_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_disposals
    ADD CONSTRAINT asset_disposals_source_work_order_id_foreign FOREIGN KEY (source_work_order_id) REFERENCES public.work_orders(id) ON DELETE SET NULL;


--
-- Name: asset_transfers asset_transfers_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_transfers
    ADD CONSTRAINT asset_transfers_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: asset_transfers asset_transfers_asset_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_transfers
    ADD CONSTRAINT asset_transfers_asset_id_foreign FOREIGN KEY (asset_id) REFERENCES public.assets(id) ON DELETE CASCADE;


--
-- Name: asset_transfers asset_transfers_from_holder_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_transfers
    ADD CONSTRAINT asset_transfers_from_holder_id_foreign FOREIGN KEY (from_holder_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: asset_transfers asset_transfers_from_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_transfers
    ADD CONSTRAINT asset_transfers_from_location_id_foreign FOREIGN KEY (from_location_id) REFERENCES public.locations(id) ON DELETE SET NULL;


--
-- Name: asset_transfers asset_transfers_requested_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_transfers
    ADD CONSTRAINT asset_transfers_requested_by_foreign FOREIGN KEY (requested_by) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: asset_transfers asset_transfers_to_holder_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_transfers
    ADD CONSTRAINT asset_transfers_to_holder_id_foreign FOREIGN KEY (to_holder_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: asset_transfers asset_transfers_to_location_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asset_transfers
    ADD CONSTRAINT asset_transfers_to_location_id_foreign FOREIGN KEY (to_location_id) REFERENCES public.locations(id) ON DELETE SET NULL;


--
-- Name: assets assets_category_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_category_id_fkey FOREIGN KEY (category_id) REFERENCES public.asset_categories(id) ON DELETE RESTRICT;


--
-- Name: assets assets_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: assets assets_location_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_location_id_fkey FOREIGN KEY (location_id) REFERENCES public.locations(id) ON DELETE SET NULL;


--
-- Name: audit_logs audit_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: depreciation_records depreciation_records_asset_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.depreciation_records
    ADD CONSTRAINT depreciation_records_asset_id_fkey FOREIGN KEY (asset_id) REFERENCES public.assets(id) ON DELETE CASCADE;


--
-- Name: work_orders fk_wo_sla; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_orders
    ADD CONSTRAINT fk_wo_sla FOREIGN KEY (sla_id) REFERENCES public.sla_policies(id) ON DELETE SET NULL;


--
-- Name: locations locations_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations
    ADD CONSTRAINT locations_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES public.locations(id) ON DELETE SET NULL;


--
-- Name: maintenance_history maintenance_history_asset_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_history
    ADD CONSTRAINT maintenance_history_asset_id_fkey FOREIGN KEY (asset_id) REFERENCES public.assets(id) ON DELETE CASCADE;


--
-- Name: maintenance_history maintenance_history_technician_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_history
    ADD CONSTRAINT maintenance_history_technician_id_fkey FOREIGN KEY (technician_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: maintenance_history maintenance_history_work_order_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_history
    ADD CONSTRAINT maintenance_history_work_order_id_fkey FOREIGN KEY (work_order_id) REFERENCES public.work_orders(id) ON DELETE CASCADE;


--
-- Name: maintenance_schedules maintenance_schedules_asset_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT maintenance_schedules_asset_id_fkey FOREIGN KEY (asset_id) REFERENCES public.assets(id) ON DELETE CASCADE;


--
-- Name: maintenance_schedules maintenance_schedules_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT maintenance_schedules_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: maintenance_schedules maintenance_schedules_default_assignee_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT maintenance_schedules_default_assignee_id_fkey FOREIGN KEY (default_assignee_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: media media_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: notifications notifications_recipient_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_recipient_id_fkey FOREIGN KEY (recipient_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: password_reset_tokens password_reset_tokens_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: role_permissions role_permissions_permission_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_permission_id_fkey FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: sla_policies sla_policies_category_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sla_policies
    ADD CONSTRAINT sla_policies_category_id_fkey FOREIGN KEY (category_id) REFERENCES public.asset_categories(id) ON DELETE CASCADE;


--
-- Name: work_order_activity_log work_order_activity_log_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_order_activity_log
    ADD CONSTRAINT work_order_activity_log_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: work_order_activity_log work_order_activity_log_work_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_order_activity_log
    ADD CONSTRAINT work_order_activity_log_work_order_id_foreign FOREIGN KEY (work_order_id) REFERENCES public.work_orders(id) ON DELETE CASCADE;


--
-- Name: work_order_checklist_items work_order_checklist_items_work_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_order_checklist_items
    ADD CONSTRAINT work_order_checklist_items_work_order_id_foreign FOREIGN KEY (work_order_id) REFERENCES public.work_orders(id) ON DELETE CASCADE;


--
-- Name: work_orders work_orders_approved_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_orders
    ADD CONSTRAINT work_orders_approved_by_fkey FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: work_orders work_orders_asset_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_orders
    ADD CONSTRAINT work_orders_asset_id_fkey FOREIGN KEY (asset_id) REFERENCES public.assets(id) ON DELETE RESTRICT;


--
-- Name: work_orders work_orders_assigned_to_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_orders
    ADD CONSTRAINT work_orders_assigned_to_fkey FOREIGN KEY (assigned_to) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: work_orders work_orders_requested_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_orders
    ADD CONSTRAINT work_orders_requested_by_fkey FOREIGN KEY (requested_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: work_orders work_orders_schedule_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_orders
    ADD CONSTRAINT work_orders_schedule_id_fkey FOREIGN KEY (schedule_id) REFERENCES public.maintenance_schedules(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--


