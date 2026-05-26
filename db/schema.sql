-- SQL schema for Case Service (Postgres example)

CREATE TABLE cases (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  status VARCHAR(50) NOT NULL DEFAULT 'NEW',
  applicant_name TEXT,
  applicant_national_id TEXT,
  contact_email TEXT,
  contact_phone TEXT,
  subject TEXT,
  description TEXT,
  metadata JSONB,
  processing_state JSONB,
  idempotency_key TEXT UNIQUE,
  last_trace_id TEXT,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE INDEX idx_cases_status ON cases(status);
CREATE INDEX idx_cases_created_at ON cases(created_at);

CREATE TABLE case_events (
  id BIGSERIAL PRIMARY KEY,
  case_id UUID REFERENCES cases(id) ON DELETE CASCADE,
  event_type TEXT,
  payload JSONB,
  actor TEXT,
  correlation_id TEXT,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

CREATE TABLE outbox_events (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  aggregate_type TEXT NOT NULL,
  aggregate_id UUID NOT NULL,
  event_type TEXT NOT NULL,
  payload JSONB NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  published BOOLEAN DEFAULT FALSE,
  published_at TIMESTAMP WITH TIME ZONE,
  publish_attempts INT DEFAULT 0,
  last_error TEXT
);

CREATE INDEX idx_outbox_published ON outbox_events(published, created_at);

CREATE TABLE idempotency (
  key TEXT PRIMARY KEY,
  tracking_id UUID,
  response_payload JSONB,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  expires_at TIMESTAMP WITH TIME ZONE
);

CREATE TABLE dlq (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  original_event JSONB,
  error TEXT,
  attempts INT,
  last_attempt TIMESTAMP WITH TIME ZONE DEFAULT now()
);
