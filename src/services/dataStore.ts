import { Diocese, Parish, Contact } from '../models';

/**
 * In-memory data store for development
 * Will be replaced with actual database in production
 */
class DataStore {
  private dioceses: Map<string, Diocese> = new Map();
  private parishes: Map<string, Parish> = new Map();
  private contacts: Map<string, Contact> = new Map();

  // Diocese operations
  getAllDioceses(): Diocese[] {
    return Array.from(this.dioceses.values());
  }

  getDioceseById(id: string): Diocese | undefined {
    return this.dioceses.get(id);
  }

  getDiocesesByState(state: string): Diocese[] {
    return this.getAllDioceses().filter(
      (d) => d.state.toLowerCase() === state.toLowerCase()
    );
  }

  getArchdioceses(): Diocese[] {
    return this.getAllDioceses().filter((d) => d.type === 'archdiocese');
  }

  createDiocese(diocese: Diocese): Diocese {
    this.dioceses.set(diocese.id, diocese);
    return diocese;
  }

  updateDiocese(id: string, updates: Partial<Diocese>): Diocese | undefined {
    const existing = this.dioceses.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.dioceses.set(id, updated);
    return updated;
  }

  deleteDiocese(id: string): boolean {
    return this.dioceses.delete(id);
  }

  // Parish operations
  getAllParishes(): Parish[] {
    return Array.from(this.parishes.values());
  }

  getParishById(id: string): Parish | undefined {
    return this.parishes.get(id);
  }

  getParishesByDiocese(dioceseId: string): Parish[] {
    return this.getAllParishes().filter((p) => p.dioceseId === dioceseId);
  }

  createParish(parish: Parish): Parish {
    this.parishes.set(parish.id, parish);
    return parish;
  }

  updateParish(id: string, updates: Partial<Parish>): Parish | undefined {
    const existing = this.parishes.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.parishes.set(id, updated);
    return updated;
  }

  deleteParish(id: string): boolean {
    return this.parishes.delete(id);
  }

  // Contact operations
  getAllContacts(): Contact[] {
    return Array.from(this.contacts.values());
  }

  getContactById(id: string): Contact | undefined {
    return this.contacts.get(id);
  }

  getContactsByDiocese(dioceseId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.dioceseId === dioceseId);
  }

  getContactsByParish(parishId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.parishId === parishId);
  }

  createContact(contact: Contact): Contact {
    this.contacts.set(contact.id, contact);
    return contact;
  }

  updateContact(id: string, updates: Partial<Contact>): Contact | undefined {
    const existing = this.contacts.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.contacts.set(id, updated);
    return updated;
  }

  deleteContact(id: string): boolean {
    return this.contacts.delete(id);
  }

  // Utility methods
  clear(): void {
    this.dioceses.clear();
    this.parishes.clear();
    this.contacts.clear();
  }
}

// Singleton instance
export const dataStore = new DataStore();
