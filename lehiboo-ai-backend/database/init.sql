-- ============================================
-- Le Hiboo AI - Database Schema
-- PostgreSQL 16
-- ============================================

-- Extension pour UUID
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================
-- Table: conversations
-- Métadonnées des conversations
-- ============================================
CREATE TABLE IF NOT EXISTS conversations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL,
    started_at TIMESTAMPTZ DEFAULT NOW(),
    last_message_at TIMESTAMPTZ DEFAULT NOW(),
    message_count INTEGER DEFAULT 0,
    summary TEXT,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_conversations_user ON conversations(user_id);
CREATE INDEX idx_conversations_last_message ON conversations(last_message_at DESC);

-- ============================================
-- Table: chat_messages
-- Tous les messages de chat
-- ============================================
CREATE TABLE IF NOT EXISTS chat_messages (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL,
    conversation_id UUID NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
    role VARCHAR(20) NOT NULL CHECK (role IN ('user', 'assistant', 'system')),
    content TEXT NOT NULL,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_messages_user ON chat_messages(user_id);
CREATE INDEX idx_messages_conversation ON chat_messages(conversation_id);
CREATE INDEX idx_messages_created ON chat_messages(created_at DESC);
CREATE INDEX idx_messages_role ON chat_messages(role);

-- ============================================
-- Table: event_impressions
-- Activités proposées aux utilisateurs (analytics)
-- ============================================
CREATE TABLE IF NOT EXISTS event_impressions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    conversation_id UUID NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
    message_id UUID NOT NULL REFERENCES chat_messages(id) ON DELETE CASCADE,
    event_id INTEGER NOT NULL,
    event_title VARCHAR(500),
    event_category VARCHAR(100),
    event_city VARCHAR(100),
    event_price DECIMAL(10,2),
    event_date DATE,
    position INTEGER,
    clicked BOOLEAN DEFAULT FALSE,
    clicked_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_impressions_user ON event_impressions(user_id);
CREATE INDEX idx_impressions_event ON event_impressions(event_id);
CREATE INDEX idx_impressions_conversation ON event_impressions(conversation_id);
CREATE INDEX idx_impressions_category ON event_impressions(event_category);
CREATE INDEX idx_impressions_city ON event_impressions(event_city);
CREATE INDEX idx_impressions_created ON event_impressions(created_at DESC);

-- ============================================
-- Table: user_profiles
-- Profils utilisateurs pour IA/ML
-- ============================================
CREATE TABLE IF NOT EXISTS user_profiles (
    user_id INTEGER PRIMARY KEY,
    preferences JSONB DEFAULT '{
        "likes": [],
        "dislikes": [],
        "favoriteCities": [],
        "favoriteCategories": [],
        "typicalBudget": null,
        "typicalGroupType": null
    }',
    insights JSONB DEFAULT '{
        "totalSearches": 0,
        "totalConversations": 0,
        "topCategories": {},
        "topCities": {},
        "averageBudget": null
    }',
    last_interaction TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- Table: search_logs
-- Historique des recherches (analytics)
-- ============================================
CREATE TABLE IF NOT EXISTS search_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER,
    conversation_id UUID REFERENCES conversations(id) ON DELETE SET NULL,
    search_params JSONB NOT NULL,
    results_count INTEGER DEFAULT 0,
    response_time_ms INTEGER,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_search_logs_user ON search_logs(user_id);
CREATE INDEX idx_search_logs_created ON search_logs(created_at DESC);
CREATE INDEX idx_search_logs_params ON search_logs USING GIN (search_params);

-- ============================================
-- Fonctions utilitaires
-- ============================================

-- Fonction pour mettre à jour updated_at automatiquement
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers pour updated_at
CREATE TRIGGER update_conversations_updated_at
    BEFORE UPDATE ON conversations
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_user_profiles_updated_at
    BEFORE UPDATE ON user_profiles
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- Vues pour analytics
-- ============================================

-- Vue: Statistiques par utilisateur
CREATE OR REPLACE VIEW user_stats AS
SELECT
    user_id,
    COUNT(DISTINCT c.id) as total_conversations,
    COUNT(m.id) as total_messages,
    COUNT(DISTINCT e.event_id) as unique_events_shown,
    SUM(CASE WHEN e.clicked THEN 1 ELSE 0 END) as total_clicks,
    ROUND(AVG(e.event_price)::numeric, 2) as avg_event_price_shown,
    MAX(m.created_at) as last_activity
FROM conversations c
LEFT JOIN chat_messages m ON m.conversation_id = c.id
LEFT JOIN event_impressions e ON e.conversation_id = c.id
GROUP BY user_id;

-- Vue: Activités les plus proposées
CREATE OR REPLACE VIEW popular_events AS
SELECT
    event_id,
    event_title,
    event_category,
    event_city,
    COUNT(*) as times_shown,
    SUM(CASE WHEN clicked THEN 1 ELSE 0 END) as times_clicked,
    ROUND(100.0 * SUM(CASE WHEN clicked THEN 1 ELSE 0 END) / COUNT(*)::numeric, 2) as click_rate
FROM event_impressions
GROUP BY event_id, event_title, event_category, event_city
ORDER BY times_shown DESC;

-- Vue: Performance par catégorie
CREATE OR REPLACE VIEW category_performance AS
SELECT
    event_category,
    COUNT(*) as impressions,
    SUM(CASE WHEN clicked THEN 1 ELSE 0 END) as clicks,
    ROUND(100.0 * SUM(CASE WHEN clicked THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)::numeric, 2) as click_rate,
    ROUND(AVG(event_price)::numeric, 2) as avg_price
FROM event_impressions
WHERE event_category IS NOT NULL
GROUP BY event_category
ORDER BY impressions DESC;

-- ============================================
-- Données initiales (optionnel)
-- ============================================

-- Rien pour l'instant, les données seront créées par l'application

-- ============================================
-- Grants (si besoin de users séparés)
-- ============================================

-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO lehiboo;
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO lehiboo;
