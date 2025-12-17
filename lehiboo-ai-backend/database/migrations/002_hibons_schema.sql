-- ============================================
-- Le Hiboo - Système Hibons (Gamification)
-- Migration 002 - PostgreSQL 16
-- ============================================

-- ============================================
-- Table: hibons_wallets
-- Portefeuille principal de chaque utilisateur
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_wallets (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL UNIQUE,

    -- Soldes
    balance INTEGER DEFAULT 0 CHECK (balance >= 0),
    balance_pending INTEGER DEFAULT 0 CHECK (balance_pending >= 0),
    lifetime_earned INTEGER DEFAULT 0,
    lifetime_spent INTEGER DEFAULT 0,

    -- Niveau et XP
    level INTEGER DEFAULT 1 CHECK (level >= 1 AND level <= 10),
    xp INTEGER DEFAULT 0 CHECK (xp >= 0),
    title VARCHAR(100) DEFAULT 'Hibou Curieux',

    -- Streaks
    current_streak INTEGER DEFAULT 0 CHECK (current_streak >= 0),
    longest_streak INTEGER DEFAULT 0 CHECK (longest_streak >= 0),
    last_activity_date DATE,
    streak_shield_until DATE,

    -- Multiplicateur actif
    multiplier DECIMAL(3,2) DEFAULT 1.00,
    multiplier_expires_at TIMESTAMPTZ,

    -- Parrainage
    referral_code VARCHAR(10) UNIQUE,
    referred_by INTEGER,

    -- Roue de la fortune
    last_free_spin_at TIMESTAMPTZ,

    -- Metadata
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_hibons_wallets_user ON hibons_wallets(user_id);
CREATE INDEX idx_hibons_wallets_level ON hibons_wallets(level DESC);
CREATE INDEX idx_hibons_wallets_balance ON hibons_wallets(balance DESC);
CREATE INDEX idx_hibons_wallets_referral ON hibons_wallets(referral_code);

-- ============================================
-- Table: hibons_transactions
-- Historique de toutes les transactions
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_transactions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL,
    wallet_id UUID NOT NULL REFERENCES hibons_wallets(id) ON DELETE CASCADE,

    -- Type: EARN, SPEND, BONUS, EXPIRE, REFUND
    type VARCHAR(20) NOT NULL CHECK (type IN ('EARN', 'SPEND', 'BONUS', 'EXPIRE', 'REFUND')),

    -- Catégorie détaillée
    category VARCHAR(50) NOT NULL,
    -- EARN: DAILY_REWARD, STREAK_BONUS, SEARCH, VIEW_EVENT, FAVORITE, SHARE,
    --       REVIEW, REVIEW_PHOTO, REVIEW_VIDEO, BOOKING, CHECKIN, REFERRAL,
    --       ACHIEVEMENT, CHALLENGE, WHEEL_SPIN
    -- SPEND: DISCOUNT, CHAT_MESSAGE, STREAK_SHIELD, WHEEL_SPIN, MULTIPLIER
    -- BONUS: WELCOME, PROMO, PARTNER

    -- Montants
    amount INTEGER NOT NULL,
    balance_after INTEGER NOT NULL,

    -- Multiplicateur appliqué
    base_amount INTEGER,
    multiplier_applied DECIMAL(3,2) DEFAULT 1.00,

    -- XP gagné (si applicable)
    xp_earned INTEGER DEFAULT 0,

    -- Contexte
    description TEXT,
    reference_type VARCHAR(50),
    reference_id VARCHAR(100),

    -- Metadata (infos supplémentaires)
    metadata JSONB DEFAULT '{}',

    -- Status
    status VARCHAR(20) DEFAULT 'completed' CHECK (status IN ('pending', 'completed', 'cancelled', 'expired')),
    expires_at TIMESTAMPTZ,

    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_hibons_tx_user ON hibons_transactions(user_id);
CREATE INDEX idx_hibons_tx_wallet ON hibons_transactions(wallet_id);
CREATE INDEX idx_hibons_tx_type ON hibons_transactions(type);
CREATE INDEX idx_hibons_tx_category ON hibons_transactions(category);
CREATE INDEX idx_hibons_tx_created ON hibons_transactions(created_at DESC);
CREATE INDEX idx_hibons_tx_status ON hibons_transactions(status);
CREATE INDEX idx_hibons_tx_reference ON hibons_transactions(reference_type, reference_id);

-- ============================================
-- Table: hibons_daily_rewards
-- État des récompenses quotidiennes par utilisateur
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_daily_rewards (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL UNIQUE,

    -- Jour actuel dans la séquence (1-7)
    sequence_day INTEGER DEFAULT 0 CHECK (sequence_day >= 0 AND sequence_day <= 7),

    -- Dernière réclamation
    last_claim_date DATE,

    -- Semaines consécutives complétées
    consecutive_weeks INTEGER DEFAULT 0,

    -- Stats du mois
    claims_this_month INTEGER DEFAULT 0,
    month_reset_date DATE,

    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_hibons_daily_user ON hibons_daily_rewards(user_id);

-- ============================================
-- Table: hibons_achievements
-- Définition des badges et achievements
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_achievements (
    id VARCHAR(50) PRIMARY KEY,

    -- Infos
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(100),

    -- Catégorie: explorer, social, streaker, spender, collector
    category VARCHAR(50) NOT NULL,

    -- Rareté: common, uncommon, rare, epic, legendary
    rarity VARCHAR(20) NOT NULL DEFAULT 'common',

    -- Condition de déblocage
    condition_type VARCHAR(50) NOT NULL,
    -- Types: searches, events_viewed, favorites, shares, reviews, photos,
    --        bookings, streak, referrals, hibons_spent, achievements, level
    condition_value INTEGER NOT NULL,
    condition_metadata JSONB DEFAULT '{}',

    -- Récompenses
    reward_hibons INTEGER DEFAULT 0,
    reward_xp INTEGER DEFAULT 0,
    reward_title VARCHAR(100),

    -- Affichage
    is_active BOOLEAN DEFAULT TRUE,
    is_secret BOOLEAN DEFAULT FALSE,
    display_order INTEGER DEFAULT 0,

    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- Table: hibons_user_achievements
-- Progression des utilisateurs sur les achievements
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_user_achievements (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL,
    achievement_id VARCHAR(50) NOT NULL REFERENCES hibons_achievements(id) ON DELETE CASCADE,

    -- Progression
    progress INTEGER DEFAULT 0,
    target INTEGER NOT NULL,

    -- Status
    unlocked_at TIMESTAMPTZ,
    claimed_at TIMESTAMPTZ,

    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),

    UNIQUE(user_id, achievement_id)
);

CREATE INDEX idx_hibons_user_ach_user ON hibons_user_achievements(user_id);
CREATE INDEX idx_hibons_user_ach_unlocked ON hibons_user_achievements(unlocked_at) WHERE unlocked_at IS NOT NULL;

-- ============================================
-- Table: hibons_challenges
-- Challenges (quotidiens, hebdomadaires, sponsorisés)
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_challenges (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),

    -- Infos
    name VARCHAR(200) NOT NULL,
    description TEXT,
    icon VARCHAR(100),

    -- Type: daily, weekly, event, sponsored
    type VARCHAR(20) NOT NULL CHECK (type IN ('daily', 'weekly', 'event', 'sponsored')),

    -- Période de validité
    starts_at TIMESTAMPTZ NOT NULL,
    ends_at TIMESTAMPTZ NOT NULL,

    -- Condition
    condition_type VARCHAR(50) NOT NULL,
    -- Types: searches, events_viewed, favorites, shares, reviews, bookings,
    --        checkins, streak_maintain, spend_hibons
    condition_value INTEGER NOT NULL,
    condition_metadata JSONB DEFAULT '{}',

    -- Récompenses
    reward_hibons INTEGER NOT NULL,
    reward_xp INTEGER DEFAULT 0,
    reward_items JSONB DEFAULT '[]',

    -- Sponsor (si applicable)
    sponsor_id INTEGER,
    sponsor_name VARCHAR(200),
    sponsor_logo VARCHAR(500),

    -- Ciblage
    min_level INTEGER DEFAULT 1,
    target_cities TEXT[],

    -- Limites
    max_participants INTEGER,

    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_hibons_challenges_type ON hibons_challenges(type);
CREATE INDEX idx_hibons_challenges_active ON hibons_challenges(starts_at, ends_at) WHERE is_active = TRUE;
CREATE INDEX idx_hibons_challenges_sponsor ON hibons_challenges(sponsor_id) WHERE sponsor_id IS NOT NULL;

-- ============================================
-- Table: hibons_user_challenges
-- Participation des utilisateurs aux challenges
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_user_challenges (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL,
    challenge_id UUID NOT NULL REFERENCES hibons_challenges(id) ON DELETE CASCADE,

    -- Progression
    progress INTEGER DEFAULT 0,
    target INTEGER NOT NULL,

    -- Status: active, completed, claimed, expired
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'completed', 'claimed', 'expired')),

    joined_at TIMESTAMPTZ DEFAULT NOW(),
    completed_at TIMESTAMPTZ,
    claimed_at TIMESTAMPTZ,

    UNIQUE(user_id, challenge_id)
);

CREATE INDEX idx_hibons_user_ch_user ON hibons_user_challenges(user_id);
CREATE INDEX idx_hibons_user_ch_challenge ON hibons_user_challenges(challenge_id);
CREATE INDEX idx_hibons_user_ch_status ON hibons_user_challenges(status);

-- ============================================
-- Table: hibons_leaderboard_snapshots
-- Snapshots des classements (pour performance)
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_leaderboard_snapshots (
    id SERIAL PRIMARY KEY,

    -- Type: daily, weekly, monthly, all_time
    period_type VARCHAR(20) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,

    -- Scope: global, city
    scope VARCHAR(20) DEFAULT 'global',
    scope_value VARCHAR(100),

    -- Top 100 en JSON
    rankings JSONB NOT NULL,
    -- Format: [{ rank, user_id, username, avatar, hibons_earned, level }]

    created_at TIMESTAMPTZ DEFAULT NOW(),

    UNIQUE(period_type, period_start, scope, scope_value)
);

CREATE INDEX idx_hibons_lb_period ON hibons_leaderboard_snapshots(period_type, period_start DESC);

-- ============================================
-- Table: hibons_wheel_config
-- Configuration des segments de la roue
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_wheel_config (
    id SERIAL PRIMARY KEY,
    segment_index INTEGER NOT NULL UNIQUE CHECK (segment_index >= 0 AND segment_index <= 7),

    -- Affichage
    label VARCHAR(100) NOT NULL,
    color VARCHAR(20) NOT NULL,
    icon VARCHAR(100),

    -- Récompense
    reward_type VARCHAR(20) NOT NULL CHECK (reward_type IN ('hibons', 'xp', 'multiplier', 'badge', 'respin', 'nothing')),
    reward_value INTEGER,
    reward_duration INTEGER, -- En secondes pour les multiplicateurs

    -- Probabilité (somme doit faire 100)
    probability DECIMAL(5,2) NOT NULL CHECK (probability >= 0 AND probability <= 100),

    is_active BOOLEAN DEFAULT TRUE
);

-- ============================================
-- Table: hibons_wheel_spins
-- Historique des tours de roue
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_wheel_spins (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL,

    -- Résultat
    segment_index INTEGER NOT NULL,
    reward_type VARCHAR(20) NOT NULL,
    reward_value INTEGER,

    -- Type de spin: free, earned, purchased
    spin_type VARCHAR(20) NOT NULL CHECK (spin_type IN ('free', 'earned', 'purchased')),
    cost_hibons INTEGER DEFAULT 0,

    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_hibons_spins_user ON hibons_wheel_spins(user_id);
CREATE INDEX idx_hibons_spins_date ON hibons_wheel_spins(created_at DESC);

-- ============================================
-- Table: hibons_referrals
-- Système de parrainage
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_referrals (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),

    -- Parrain
    referrer_user_id INTEGER NOT NULL,

    -- Filleul
    referred_user_id INTEGER NOT NULL UNIQUE,

    -- Récompenses (configurables)
    referrer_reward INTEGER DEFAULT 500,
    referred_reward INTEGER DEFAULT 200,

    -- Status: pending (inscrit), qualified (première action), rewarded
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'qualified', 'rewarded')),

    qualified_at TIMESTAMPTZ,
    rewarded_at TIMESTAMPTZ,

    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_hibons_ref_referrer ON hibons_referrals(referrer_user_id);
CREATE INDEX idx_hibons_ref_referred ON hibons_referrals(referred_user_id);
CREATE INDEX idx_hibons_ref_status ON hibons_referrals(status);

-- ============================================
-- Table: hibons_action_limits
-- Limites quotidiennes d'actions par utilisateur
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_action_limits (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    action_date DATE NOT NULL,

    -- Compteurs par action
    searches INTEGER DEFAULT 0,
    views INTEGER DEFAULT 0,
    favorites INTEGER DEFAULT 0,
    shares INTEGER DEFAULT 0,
    reviews INTEGER DEFAULT 0,
    review_photos INTEGER DEFAULT 0,
    review_videos INTEGER DEFAULT 0,
    chat_feedbacks INTEGER DEFAULT 0,

    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),

    UNIQUE(user_id, action_date)
);

CREATE INDEX idx_hibons_limits_user_date ON hibons_action_limits(user_id, action_date);

-- ============================================
-- Table: hibons_shop_items
-- Catalogue des items achetables avec Hibons
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_shop_items (
    id VARCHAR(50) PRIMARY KEY,

    -- Infos
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(100),

    -- Type: discount, feature, boost, cosmetic, lottery
    type VARCHAR(20) NOT NULL,

    -- Prix en Hibons
    cost INTEGER NOT NULL CHECK (cost > 0),

    -- Valeur (dépend du type)
    value JSONB NOT NULL,
    -- discount: { "percent": 5 }
    -- feature: { "type": "chat_message", "quantity": 1 }
    -- boost: { "multiplier": 1.5, "duration": 3600 }
    -- cosmetic: { "badge_id": "..." }
    -- lottery: { "contest_id": "..." }

    -- Limites
    max_per_user INTEGER,
    max_per_day INTEGER,

    -- Disponibilité
    is_active BOOLEAN DEFAULT TRUE,
    available_from TIMESTAMPTZ,
    available_until TIMESTAMPTZ,

    display_order INTEGER DEFAULT 0,

    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- Table: hibons_purchases
-- Achats effectués dans le shop
-- ============================================
CREATE TABLE IF NOT EXISTS hibons_purchases (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL,
    item_id VARCHAR(50) NOT NULL REFERENCES hibons_shop_items(id),

    -- Montant payé
    cost INTEGER NOT NULL,

    -- Status: pending, completed, used, expired, refunded
    status VARCHAR(20) DEFAULT 'completed',

    -- Pour les items à usage unique
    used_at TIMESTAMPTZ,
    expires_at TIMESTAMPTZ,

    -- Contexte d'utilisation
    used_on_reference_type VARCHAR(50),
    used_on_reference_id VARCHAR(100),

    metadata JSONB DEFAULT '{}',

    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_hibons_purchases_user ON hibons_purchases(user_id);
CREATE INDEX idx_hibons_purchases_item ON hibons_purchases(item_id);
CREATE INDEX idx_hibons_purchases_status ON hibons_purchases(status);

-- ============================================
-- Triggers pour updated_at
-- ============================================

CREATE TRIGGER update_hibons_wallets_updated_at
    BEFORE UPDATE ON hibons_wallets
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_hibons_daily_rewards_updated_at
    BEFORE UPDATE ON hibons_daily_rewards
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_hibons_user_achievements_updated_at
    BEFORE UPDATE ON hibons_user_achievements
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_hibons_challenges_updated_at
    BEFORE UPDATE ON hibons_challenges
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_hibons_action_limits_updated_at
    BEFORE UPDATE ON hibons_action_limits
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- Fonctions utilitaires Hibons
-- ============================================

-- Générer un code de parrainage unique
CREATE OR REPLACE FUNCTION generate_referral_code()
RETURNS VARCHAR(10) AS $$
DECLARE
    chars VARCHAR(36) := 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    code VARCHAR(10) := '';
    i INTEGER;
BEGIN
    FOR i IN 1..8 LOOP
        code := code || substr(chars, floor(random() * length(chars) + 1)::integer, 1);
    END LOOP;
    RETURN code;
END;
$$ LANGUAGE plpgsql;

-- Créditer des Hibons (avec multiplicateur)
CREATE OR REPLACE FUNCTION credit_hibons(
    p_user_id INTEGER,
    p_amount INTEGER,
    p_category VARCHAR,
    p_description TEXT DEFAULT NULL,
    p_reference_type VARCHAR DEFAULT NULL,
    p_reference_id VARCHAR DEFAULT NULL,
    p_xp INTEGER DEFAULT 0
) RETURNS TABLE(transaction_id UUID, final_amount INTEGER, new_balance INTEGER, xp_earned INTEGER) AS $$
DECLARE
    v_wallet hibons_wallets%ROWTYPE;
    v_multiplier DECIMAL(3,2);
    v_final_amount INTEGER;
    v_tx_id UUID;
BEGIN
    -- Récupérer ou créer le wallet
    SELECT * INTO v_wallet FROM hibons_wallets WHERE user_id = p_user_id FOR UPDATE;

    IF NOT FOUND THEN
        INSERT INTO hibons_wallets (user_id, referral_code)
        VALUES (p_user_id, generate_referral_code())
        RETURNING * INTO v_wallet;
    END IF;

    -- Calculer le multiplicateur actif
    IF v_wallet.multiplier_expires_at IS NOT NULL AND v_wallet.multiplier_expires_at > NOW() THEN
        v_multiplier := v_wallet.multiplier;
    ELSE
        v_multiplier := 1.00;
    END IF;

    -- Appliquer le multiplicateur
    v_final_amount := ROUND(p_amount * v_multiplier);

    -- Mettre à jour le wallet
    UPDATE hibons_wallets
    SET balance = balance + v_final_amount,
        lifetime_earned = lifetime_earned + v_final_amount,
        xp = xp + p_xp,
        updated_at = NOW()
    WHERE user_id = p_user_id
    RETURNING * INTO v_wallet;

    -- Créer la transaction
    INSERT INTO hibons_transactions (
        user_id, wallet_id, type, category, amount, balance_after,
        base_amount, multiplier_applied, xp_earned,
        description, reference_type, reference_id
    ) VALUES (
        p_user_id, v_wallet.id, 'EARN', p_category, v_final_amount,
        v_wallet.balance, p_amount, v_multiplier, p_xp,
        p_description, p_reference_type, p_reference_id
    ) RETURNING id INTO v_tx_id;

    RETURN QUERY SELECT v_tx_id, v_final_amount, v_wallet.balance, p_xp;
END;
$$ LANGUAGE plpgsql;

-- Débiter des Hibons
CREATE OR REPLACE FUNCTION debit_hibons(
    p_user_id INTEGER,
    p_amount INTEGER,
    p_category VARCHAR,
    p_description TEXT DEFAULT NULL,
    p_reference_type VARCHAR DEFAULT NULL,
    p_reference_id VARCHAR DEFAULT NULL
) RETURNS TABLE(transaction_id UUID, new_balance INTEGER) AS $$
DECLARE
    v_wallet hibons_wallets%ROWTYPE;
    v_tx_id UUID;
BEGIN
    -- Récupérer le wallet avec verrou
    SELECT * INTO v_wallet FROM hibons_wallets WHERE user_id = p_user_id FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Wallet not found for user %', p_user_id;
    END IF;

    IF v_wallet.balance < p_amount THEN
        RAISE EXCEPTION 'Insufficient balance: % < %', v_wallet.balance, p_amount;
    END IF;

    -- Débiter
    UPDATE hibons_wallets
    SET balance = balance - p_amount,
        lifetime_spent = lifetime_spent + p_amount,
        updated_at = NOW()
    WHERE user_id = p_user_id
    RETURNING * INTO v_wallet;

    -- Créer la transaction
    INSERT INTO hibons_transactions (
        user_id, wallet_id, type, category, amount, balance_after,
        description, reference_type, reference_id
    ) VALUES (
        p_user_id, v_wallet.id, 'SPEND', p_category, -p_amount,
        v_wallet.balance, p_description, p_reference_type, p_reference_id
    ) RETURNING id INTO v_tx_id;

    RETURN QUERY SELECT v_tx_id, v_wallet.balance;
END;
$$ LANGUAGE plpgsql;

-- Mettre à jour le niveau selon l'XP
CREATE OR REPLACE FUNCTION update_user_level(p_user_id INTEGER)
RETURNS TABLE(new_level INTEGER, new_title VARCHAR) AS $$
DECLARE
    v_xp INTEGER;
    v_level INTEGER;
    v_title VARCHAR(100);
BEGIN
    SELECT xp INTO v_xp FROM hibons_wallets WHERE user_id = p_user_id;

    -- Calcul du niveau basé sur l'XP
    v_level := CASE
        WHEN v_xp >= 5500 THEN 10
        WHEN v_xp >= 4000 THEN 9
        WHEN v_xp >= 3000 THEN 8
        WHEN v_xp >= 2200 THEN 7
        WHEN v_xp >= 1500 THEN 6
        WHEN v_xp >= 1000 THEN 5
        WHEN v_xp >= 600 THEN 4
        WHEN v_xp >= 300 THEN 3
        WHEN v_xp >= 100 THEN 2
        ELSE 1
    END;

    v_title := CASE v_level
        WHEN 10 THEN 'Maître Hibou'
        WHEN 9 THEN 'Grand Hibou'
        WHEN 8 THEN 'Hibou Légendaire'
        WHEN 7 THEN 'Hibou Elite'
        WHEN 6 THEN 'Hibou VIP'
        WHEN 5 THEN 'Hibou Expert'
        WHEN 4 THEN 'Hibou Connaisseur'
        WHEN 3 THEN 'Hibou Aventurier'
        WHEN 2 THEN 'Hibou Explorateur'
        ELSE 'Hibou Curieux'
    END;

    UPDATE hibons_wallets
    SET level = v_level, title = v_title, updated_at = NOW()
    WHERE user_id = p_user_id;

    RETURN QUERY SELECT v_level, v_title;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- Données initiales
-- ============================================

-- Configuration de la roue de la fortune (8 segments)
INSERT INTO hibons_wheel_config (segment_index, label, color, icon, reward_type, reward_value, reward_duration, probability) VALUES
(0, '10 Hibons', '#FF6B6B', 'coin', 'hibons', 10, NULL, 25.00),
(1, '25 Hibons', '#4ECDC4', 'coin', 'hibons', 25, NULL, 20.00),
(2, '50 Hibons', '#45B7D1', 'coins', 'hibons', 50, NULL, 15.00),
(3, '100 Hibons', '#96CEB4', 'coins', 'hibons', 100, NULL, 8.00),
(4, 'x1.5 (1h)', '#FFEAA7', 'zap', 'multiplier', 150, 3600, 12.00),
(5, 'x2 (30min)', '#DDA0DD', 'zap', 'multiplier', 200, 1800, 8.00),
(6, '+50 XP', '#F0E68C', 'star', 'xp', 50, NULL, 7.00),
(7, 'JACKPOT 500', '#FFD700', 'trophy', 'hibons', 500, NULL, 5.00)
ON CONFLICT (segment_index) DO NOTHING;

-- Achievements de base
INSERT INTO hibons_achievements (id, name, description, category, rarity, condition_type, condition_value, reward_hibons, reward_xp, display_order) VALUES
-- Explorer
('first_search', 'Premier Pas', 'Effectue ta première recherche', 'explorer', 'common', 'searches', 1, 20, 10, 1),
('explorer_10', 'Explorateur', 'Consulte 10 événements', 'explorer', 'common', 'events_viewed', 10, 50, 25, 2),
('explorer_50', 'Grand Explorateur', 'Consulte 50 événements', 'explorer', 'uncommon', 'events_viewed', 50, 100, 50, 3),
('explorer_100', 'Aventurier', 'Consulte 100 événements', 'explorer', 'rare', 'events_viewed', 100, 200, 100, 4),
('city_hopper', 'Globe-Trotter', 'Explore des événements dans 5 villes différentes', 'explorer', 'uncommon', 'unique_cities', 5, 100, 50, 5),

-- Social
('first_review', 'Critique en Herbe', 'Poste ton premier avis', 'social', 'common', 'reviews', 1, 30, 15, 10),
('reviewer_5', 'Critique Averti', 'Poste 5 avis', 'social', 'uncommon', 'reviews', 5, 75, 40, 11),
('reviewer_10', 'Critique Expert', 'Poste 10 avis', 'social', 'rare', 'reviews', 10, 150, 75, 12),
('photographer', 'Photographe', 'Ajoute 10 photos à tes avis', 'social', 'uncommon', 'photos', 10, 100, 50, 13),
('influencer_3', 'Parrain Débutant', 'Parraine 3 amis', 'social', 'uncommon', 'referrals', 3, 200, 100, 14),
('influencer_5', 'Influenceur', 'Parraine 5 amis', 'social', 'rare', 'referrals', 5, 500, 200, 15),

-- Streaker
('streak_7', 'Habitué', 'Maintiens un streak de 7 jours', 'streaker', 'uncommon', 'streak', 7, 100, 50, 20),
('streak_14', 'Fidèle', 'Maintiens un streak de 14 jours', 'streaker', 'rare', 'streak', 14, 200, 100, 21),
('streak_30', 'Assidu', 'Maintiens un streak de 30 jours', 'streaker', 'epic', 'streak', 30, 500, 200, 22),
('streak_100', 'Légendaire', 'Maintiens un streak de 100 jours', 'streaker', 'legendary', 'streak', 100, 1000, 500, 23),

-- Spender
('first_booking', 'Première Sortie', 'Effectue ta première réservation', 'spender', 'common', 'bookings', 1, 50, 25, 30),
('booker_5', 'Sorteur', 'Effectue 5 réservations', 'spender', 'uncommon', 'bookings', 5, 150, 75, 31),
('booker_10', 'Sorteur Confirmé', 'Effectue 10 réservations', 'spender', 'rare', 'bookings', 10, 300, 150, 32),

-- Collector
('badge_5', 'Collectionneur', 'Débloque 5 achievements', 'collector', 'uncommon', 'achievements', 5, 100, 50, 40),
('badge_10', 'Chasseur de Badges', 'Débloque 10 achievements', 'collector', 'rare', 'achievements', 10, 250, 100, 41),

-- Secret
('night_owl', 'Noctambule', 'Réserve une activité après minuit', 'secret', 'rare', 'booking_after_midnight', 1, 100, 50, 50),
('early_bird', 'Lève-Tôt', 'Réserve une activité avant 7h du matin', 'secret', 'rare', 'booking_before_7am', 1, 100, 50, 51)
ON CONFLICT (id) DO NOTHING;

-- Items du shop
INSERT INTO hibons_shop_items (id, name, description, type, cost, value, max_per_day, display_order) VALUES
('discount_5', '-5% sur réservation', 'Applique une réduction de 5% sur ta prochaine réservation', 'discount', 200, '{"percent": 5}', 3, 1),
('discount_10', '-10% sur réservation', 'Applique une réduction de 10% sur ta prochaine réservation', 'discount', 400, '{"percent": 10}', 2, 2),
('discount_15', '-15% sur réservation', 'Applique une réduction de 15% sur ta prochaine réservation', 'discount', 600, '{"percent": 15}', 1, 3),
('discount_20', '-20% sur réservation', 'Applique une réduction de 20% sur ta prochaine réservation', 'discount', 800, '{"percent": 20}', 1, 4),
('chat_message_1', '+1 message Petit Boo', 'Un message supplémentaire avec Petit Boo', 'feature', 50, '{"type": "chat_message", "quantity": 1}', 10, 10),
('chat_message_5', '+5 messages Petit Boo', 'Pack de 5 messages avec Petit Boo', 'feature', 200, '{"type": "chat_message", "quantity": 5}', 5, 11),
('chat_unlimited_24h', 'Chat illimité 24h', 'Messages illimités avec Petit Boo pendant 24h', 'feature', 300, '{"type": "chat_unlimited", "duration": 86400}', 1, 12),
('streak_shield', 'Streak Shield', 'Protège ton streak pendant 1 jour', 'boost', 150, '{"type": "streak_shield", "days": 1}', 1, 20),
('multiplier_1_5', 'Boost x1.5 (1h)', 'Multiplie tes gains par 1.5 pendant 1 heure', 'boost', 100, '{"multiplier": 1.5, "duration": 3600}', 3, 21),
('multiplier_2', 'Boost x2 (30min)', 'Double tes gains pendant 30 minutes', 'boost', 150, '{"multiplier": 2.0, "duration": 1800}', 2, 22),
('wheel_spin', 'Tour de roue', 'Tourne la roue de la fortune', 'feature', 100, '{"type": "wheel_spin"}', 5, 30)
ON CONFLICT (id) DO NOTHING;

-- ============================================
-- Vue pour le leaderboard en temps réel
-- ============================================
CREATE OR REPLACE VIEW hibons_leaderboard_live AS
SELECT
    w.user_id,
    w.balance,
    w.level,
    w.title,
    w.lifetime_earned,
    w.current_streak,
    (SELECT COUNT(*) FROM hibons_user_achievements ua WHERE ua.user_id = w.user_id AND ua.unlocked_at IS NOT NULL) as achievements_count,
    w.created_at as member_since
FROM hibons_wallets w
ORDER BY w.lifetime_earned DESC;
