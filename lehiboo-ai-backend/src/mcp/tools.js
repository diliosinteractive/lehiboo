/**
 * MCP Tools - Model Context Protocol
 * Tools disponibles pour l'IA afin d'accéder aux données WordPress/EventList
 */

import wordpressService from '../services/wordpress-service.js';
import logger from '../utils/logger.js';

/**
 * Définition des MCP Tools
 * Chaque tool a:
 * - name: Nom unique
 * - description: Ce que fait le tool (pour l'IA)
 * - parameters: Schema des paramètres attendus
 * - execute: Fonction d'exécution
 */

export const mcpTools = [
  {
    name: 'search_events',
    description:
      'Search for events in the EventList database. Use this to find activities based on user preferences like type (sport, culture, gastronomie), dates, age restrictions, and location. Returns a list of matching events with details.',
    parameters: {
      type: 'object',
      properties: {
        type: {
          type: 'string',
          description:
            'Type of activity: sport, culture, gastronomie, nature, detente',
          enum: ['sport', 'culture', 'gastronomie', 'nature', 'detente'],
        },
        startDate: {
          type: 'string',
          description: 'Start date in YYYY-MM-DD format',
        },
        endDate: {
          type: 'string',
          description: 'End date in YYYY-MM-DD format',
        },
        minAge: {
          type: 'number',
          description: 'Minimum age for participants',
        },
        maxAge: {
          type: 'number',
          description: 'Maximum age for participants',
        },
        location: {
          type: 'string',
          description: 'Location or city name',
        },
        limit: {
          type: 'number',
          description: 'Maximum number of results to return (default: 5)',
          default: 5,
        },
      },
      required: [],
    },
    async execute(params) {
      try {
        logger.info('MCP Tool: search_events called', params);

        const events = await wordpressService.searchEvents({
          type: params.type,
          startDate: params.startDate,
          endDate: params.endDate,
          minAge: params.minAge,
          maxAge: params.maxAge,
          location: params.location,
          limit: params.limit || 5,
        });

        // Formater pour l'IA
        const formattedEvents = events.map((event) =>
          wordpressService.formatEventForAI(event)
        );

        logger.info('MCP Tool: search_events completed', {
          count: formattedEvents.length,
        });

        return {
          success: true,
          count: formattedEvents.length,
          events: formattedEvents,
        };
      } catch (error) {
        logger.error('MCP Tool: search_events failed', {
          error: error.message,
        });
        return {
          success: false,
          error: error.message,
          events: [],
        };
      }
    },
  },

  {
    name: 'get_event_details',
    description:
      'Get detailed information about a specific event by its ID. Use this when you need more details about an event to answer user questions.',
    parameters: {
      type: 'object',
      properties: {
        eventId: {
          type: 'string',
          description: 'The unique ID of the event',
        },
      },
      required: ['eventId'],
    },
    async execute(params) {
      try {
        logger.info('MCP Tool: get_event_details called', params);

        const event = await wordpressService.getEventDetails(params.eventId);

        if (!event) {
          return {
            success: false,
            error: 'Event not found',
            event: null,
          };
        }

        logger.info('MCP Tool: get_event_details completed', {
          eventId: params.eventId,
        });

        return {
          success: true,
          event,
        };
      } catch (error) {
        logger.error('MCP Tool: get_event_details failed', {
          error: error.message,
        });
        return {
          success: false,
          error: error.message,
          event: null,
        };
      }
    },
  },

  {
    name: 'filter_by_age',
    description:
      'Filter a list of events based on age restrictions. Use this to ensure all recommended events are appropriate for the user age.',
    parameters: {
      type: 'object',
      properties: {
        eventIds: {
          type: 'array',
          items: { type: 'string' },
          description: 'Array of event IDs to filter',
        },
        age: {
          type: 'number',
          description: 'User age to filter against',
        },
      },
      required: ['eventIds', 'age'],
    },
    async execute(params) {
      try {
        logger.info('MCP Tool: filter_by_age called', params);

        // Récupérer les détails de chaque événement
        const eventsPromises = params.eventIds.map((id) =>
          wordpressService.getEventDetails(id)
        );
        const events = await Promise.all(eventsPromises);

        // Filtrer par âge
        const filteredEvents = wordpressService.filterEventsByAge(
          events.filter((e) => e !== null),
          params.age,
          params.age
        );

        logger.info('MCP Tool: filter_by_age completed', {
          original: events.length,
          filtered: filteredEvents.length,
        });

        return {
          success: true,
          originalCount: events.length,
          filteredCount: filteredEvents.length,
          events: filteredEvents,
        };
      } catch (error) {
        logger.error('MCP Tool: filter_by_age failed', {
          error: error.message,
        });
        return {
          success: false,
          error: error.message,
          events: [],
        };
      }
    },
  },

  {
    name: 'check_availability',
    description:
      'Check if an event has available spots for booking. Use this before recommending an event to ensure it can actually be booked.',
    parameters: {
      type: 'object',
      properties: {
        eventId: {
          type: 'string',
          description: 'The unique ID of the event',
        },
        numberOfPeople: {
          type: 'number',
          description: 'Number of people wanting to book',
          default: 1,
        },
      },
      required: ['eventId'],
    },
    async execute(params) {
      try {
        logger.info('MCP Tool: check_availability called', params);

        const event = await wordpressService.getEventDetails(params.eventId);

        if (!event) {
          return {
            success: false,
            available: false,
            message: 'Event not found',
          };
        }

        // Vérifier disponibilité
        // À adapter selon votre système de réservation
        const available = event.availability === 'available';
        const spotsLeft = event.spots_remaining || 0;
        const canBook =
          available && spotsLeft >= (params.numberOfPeople || 1);

        return {
          success: true,
          available: canBook,
          spotsLeft,
          message: canBook
            ? 'Event is available'
            : available
            ? `Only ${spotsLeft} spots left`
            : 'Event is fully booked',
        };
      } catch (error) {
        logger.error('MCP Tool: check_availability failed', {
          error: error.message,
        });
        return {
          success: false,
          available: false,
          error: error.message,
        };
      }
    },
  },

  {
    name: 'calculate_distance',
    description:
      'Calculate approximate distance from user location to event venue. Use this to sort recommendations by proximity.',
    parameters: {
      type: 'object',
      properties: {
        eventId: {
          type: 'string',
          description: 'The unique ID of the event',
        },
        userLocation: {
          type: 'string',
          description: 'User location (city name or address)',
        },
      },
      required: ['eventId', 'userLocation'],
    },
    async execute(params) {
      try {
        logger.info('MCP Tool: calculate_distance called', params);

        // TODO: Implémenter avec Google Maps API ou équivalent
        // Pour l'instant, retourner une distance simulée

        return {
          success: true,
          distance: '5-10 km',
          duration: '15 min',
          message: 'Distance calculation not yet implemented (placeholder)',
        };
      } catch (error) {
        logger.error('MCP Tool: calculate_distance failed', {
          error: error.message,
        });
        return {
          success: false,
          error: error.message,
        };
      }
    },
  },

  {
    name: 'suggest_itinerary',
    description:
      'Create an optimized itinerary from multiple events for a weekend or vacation package. Orders events logically based on dates, times, and locations.',
    parameters: {
      type: 'object',
      properties: {
        eventIds: {
          type: 'array',
          items: { type: 'string' },
          description: 'Array of event IDs to include in itinerary',
        },
        startDate: {
          type: 'string',
          description: 'Package start date (YYYY-MM-DD)',
        },
        endDate: {
          type: 'string',
          description: 'Package end date (YYYY-MM-DD)',
        },
      },
      required: ['eventIds'],
    },
    async execute(params) {
      try {
        logger.info('MCP Tool: suggest_itinerary called', params);

        // Récupérer les événements
        const eventsPromises = params.eventIds.map((id) =>
          wordpressService.getEventDetails(id)
        );
        const events = await Promise.all(eventsPromises);

        // Trier par date puis par heure
        const sortedEvents = events
          .filter((e) => e !== null)
          .sort((a, b) => {
            const dateA = new Date(a.date);
            const dateB = new Date(b.date);
            return dateA - dateB;
          });

        // Créer l'itinéraire
        const itinerary = sortedEvents.map((event, index) => ({
          day: Math.floor(index / 2) + 1, // 2 activités max par jour
          time: index % 2 === 0 ? 'morning' : 'afternoon',
          event: event,
        }));

        return {
          success: true,
          itinerary,
          totalEvents: sortedEvents.length,
          totalDays: Math.ceil(sortedEvents.length / 2),
        };
      } catch (error) {
        logger.error('MCP Tool: suggest_itinerary failed', {
          error: error.message,
        });
        return {
          success: false,
          error: error.message,
          itinerary: [],
        };
      }
    },
  },
];

/**
 * Exécuter un MCP Tool
 */
export async function executeTool(toolName, parameters) {
  const tool = mcpTools.find((t) => t.name === toolName);

  if (!tool) {
    logger.warn('MCP Tool not found', { toolName });
    return {
      success: false,
      error: `Tool '${toolName}' not found`,
    };
  }

  logger.info('Executing MCP Tool', { toolName, parameters });

  try {
    const result = await tool.execute(parameters);
    return result;
  } catch (error) {
    logger.error('MCP Tool execution failed', {
      toolName,
      error: error.message,
    });
    return {
      success: false,
      error: error.message,
    };
  }
}

/**
 * Obtenir la liste des tools disponibles (pour l'IA)
 */
export function getToolsDefinitions() {
  return mcpTools.map((tool) => ({
    name: tool.name,
    description: tool.description,
    parameters: tool.parameters,
  }));
}
